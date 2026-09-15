<?php
/*
======================================================================
 ALGORITHMS.PHP
 Light-weight, easy to explain algorithms used across the platform.
 Written for the final year project report — each function below
 documents the idea in plain words so it is easy to present/defend.
======================================================================
*/

/* ----------------------------------------------------------------
   1) CONTENT-BASED RECOMMENDATION ALGORITHM
   ----------------------------------------------------------------
   Idea : Two properties are "similar" if they share the same type
   (home/land), the same offer (sale/resale), are in the same area
   (matched from the address string) and are close in price.

   Each factor contributes points to a similarity score:
      - same property type........... 35 points
      - same offer type.............. 15 points
      - same area/locality........... 30 points
      - price within 20% range....... 20 points (scaled by closeness)

   The candidate properties are sorted by total score (highest
   first) and the top N are returned as "Similar / Recommended
   Properties" -- a simplified version of content-based filtering
   used by real e-commerce & real-estate sites.
------------------------------------------------------------------- */
function get_recommended_properties($conn, $property, $limit = 3){
   $area = trim(explode(',', $property['address'])[0] ?? $property['address']);

   $stmt = $conn->prepare("SELECT * FROM `property` WHERE approved = 1 AND id != ?");
   $stmt->execute([$property['id']]);
   $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

   $base_price = (float) $property['price'];
   $scored = [];

   foreach ($candidates as $candidate) {
      $score = 0;

      // same property type
      if ($candidate['type'] === $property['type']) $score += 35;

      // same offer type
      if ($candidate['offer'] === $property['offer']) $score += 15;

      // same locality (compare first chunk of address, case-insensitive)
      if (stripos($candidate['address'], $area) !== false) $score += 30;

      // price closeness (within 20% -> full/partial points)
      $cand_price = (float) $candidate['price'];
      if ($base_price > 0) {
         $diff_ratio = abs($cand_price - $base_price) / $base_price;
         if ($diff_ratio <= 0.20) {
            $score += round(20 * (1 - ($diff_ratio / 0.20)));
         }
      }

      if ($score > 0) {
         $candidate['similarity_score'] = $score;
         $scored[] = $candidate;
      }
   }

   // sort descending by similarity score (simple selection-style sort)
   usort($scored, function($a, $b){
      return $b['similarity_score'] <=> $a['similarity_score'];
   });

   return array_slice($scored, 0, $limit);
}


/* ----------------------------------------------------------------
   2) SEARCH RELEVANCE RANKING ALGORITHM
   ----------------------------------------------------------------
   Idea : Instead of just filtering rows with SQL "WHERE", every
   result is given a relevance score against the searcher's
   criteria, then results are sorted by that score (best match
   first) -- similar to how search engines rank results.

   Scoring weights:
      - location match ................ 30
      - property type match ........... 20
      - offer type match .............. 15
      - BHK match ...................... 10
      - status match ................... 10
      - furnished match ................ 10
      - price inside requested budget .. 5  (bonus, closer to the
                                              middle of the budget = higher)
------------------------------------------------------------------- */
function rank_search_results($properties, $criteria){
   $scored = [];

   $mid_budget = null;
   if (!empty($criteria['min']) && !empty($criteria['max'])) {
      $mid_budget = ((float)$criteria['min'] + (float)$criteria['max']) / 2;
   }

   foreach ($properties as $property) {
      $score = 0;

      if (!empty($criteria['location']) && stripos($property['address'], $criteria['location']) !== false) $score += 30;
      if (!empty($criteria['type']) && strcasecmp($property['type'], $criteria['type']) === 0) $score += 20;
      if (!empty($criteria['offer']) && strcasecmp($property['offer'], $criteria['offer']) === 0) $score += 15;
      if (!empty($criteria['bhk']) && (string)$property['bhk'] === (string)$criteria['bhk']) $score += 10;
      if (!empty($criteria['status']) && strcasecmp($property['status'], $criteria['status']) === 0) $score += 10;
      if (!empty($criteria['furnished']) && strcasecmp($property['furnished'], $criteria['furnished']) === 0) $score += 10;

      if ($mid_budget !== null && $mid_budget > 0) {
         $price = (float) $property['price'];
         $closeness = 1 - (abs($price - $mid_budget) / $mid_budget);
         if ($closeness > 0) $score += round(5 * $closeness);
      }

      $property['match_score'] = min(100, $score);
      $scored[] = $property;
   }

   usort($scored, function($a, $b){
      return $b['match_score'] <=> $a['match_score'];
   });

   return $scored;
}


/* ----------------------------------------------------------------
   3) PRICE ESTIMATION ALGORITHM (simple heuristic regression)
   ----------------------------------------------------------------
   Idea : Estimate a "fair market value" for a property using the
   average price-per-sqft of similar, already-listed properties
   (same type + same locality). This is a lightweight stand-in for
   a full regression model, but demonstrates the same principle:
   predicting price from comparable data points.

      estimated_price = avg(price_per_sqft of comparable listings)
                         x area_of_this_property

   If fewer than 2 comparable listings exist, it falls back to the
   city-wide average price-per-sqft for that property type.
------------------------------------------------------------------- */
function estimate_property_price($conn, $property){
   $area_field = $property['type'] === 'land' ? 'total_area' : 'carpet';
   $this_area  = (float) ($property[$area_field] ?? 0);
   if ($this_area <= 0) return null;

   $locality = trim(explode(',', $property['address'])[0] ?? $property['address']);

   // comparable listings: same type + same locality, excluding this one
   $stmt = $conn->prepare("SELECT price, carpet, total_area FROM `property`
                            WHERE approved = 1 AND type = ? AND address LIKE ? AND id != ?");
   $stmt->execute([$property['type'], "%$locality%", $property['id']]);
   $comparables = $stmt->fetchAll(PDO::FETCH_ASSOC);

   $ratios = [];
   foreach ($comparables as $c) {
      $a = (float) ($property['type'] === 'land' ? $c['total_area'] : $c['carpet']);
      $p = (float) $c['price'];
      if ($a > 0 && $p > 0) $ratios[] = $p / $a;
   }

   // fallback: city-wide average for the same property type
   if (count($ratios) < 2) {
      $stmt = $conn->prepare("SELECT price, carpet, total_area FROM `property`
                               WHERE approved = 1 AND type = ? AND id != ?");
      $stmt->execute([$property['type'], $property['id']]);
      $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $ratios = [];
      foreach ($all as $c) {
         $a = (float) ($property['type'] === 'land' ? $c['total_area'] : $c['carpet']);
         $p = (float) $c['price'];
         if ($a > 0 && $p > 0) $ratios[] = $p / $a;
      }
   }

   if (count($ratios) === 0) return null;

   $avg_rate = array_sum($ratios) / count($ratios);
   $estimated_price = round($avg_rate * $this_area);
   $listed_price = (float) $property['price'];

   $diff_pct = $listed_price > 0 ? (($listed_price - $estimated_price) / $estimated_price) * 100 : 0;

   $verdict = 'fair';
   if ($diff_pct > 10) $verdict = 'high';       // listed notably above estimate
   elseif ($diff_pct < -10) $verdict = 'low';   // listed notably below estimate

   return [
      'estimated_price' => $estimated_price,
      'diff_pct'         => round($diff_pct, 1),
      'verdict'          => $verdict,
      'sample_size'      => count($ratios),
   ];
}
