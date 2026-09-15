let menu = document.querySelector('.header .menu');

document.querySelector('#menu-btn').onclick = () =>{
   menu.classList.toggle('active');
}

window.onscroll = () =>{
   menu.classList.remove('active');
}

// restict non-numeric input.(PrashantttDaiiiiiiiii)
document.querySelectorAll('input[inputmode="numeric"], input[inputmode="decimal"]').forEach(input => {
    input.addEventListener('keydown', (e) => {
        const allowDecimals = input.getAttribute('inputmode') === 'decimal';
        const blocked = ['-', '+', 'e', 'E'];
        if (!allowDecimals) blocked.push('.');
        if (blocked.includes(e.key)) {
            e.preventDefault();
        }
    });

    input.addEventListener('input', () => {
        const allowDecimals = input.getAttribute('inputmode') === 'decimal';
        const pattern = allowDecimals ? /[^0-9.]/g : /[^0-9]/g;
        input.value = input.value.replace(pattern, '');
    });
});

const formEl = document.querySelector('form');
if (formEl) {
   formEl.addEventListener('submit', function(e) {
      const required = this.querySelectorAll('[required]');
      let hasEmpty = false;

      required.forEach(field => {
         if (!field.value.trim()) {
            hasEmpty = true;
            field.style.border = '1px solid #e74c3c';
         } else {
            field.style.border = '';
         }
      });

      if (hasEmpty) {
         e.preventDefault();
         const msg = document.getElementById('form-error-msg');
         if (msg) {
            msg.style.display = 'block';
            msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => { msg.style.display = 'none'; }, 4000);
         }
      }
   });
}