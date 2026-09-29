document.addEventListener('DOMContentLoaded', () => {

  
  // Setup individual accordions
  const accordions = document.querySelectorAll('.widget-accordion:not([data-accordion-init])');
  accordions.forEach(item => {
    item.setAttribute('data-accordion-init', 'true');
    let btn = item.querySelector('.js-reveal__trigger');
    if (!btn) return;

    btn.innerHTML = '<button>' + btn.innerHTML + '</button>';
    btn.addEventListener('click', () => {
      let expanded = btn.getAttribute('aria-expanded') === 'true';
      let target = item.querySelector('.js-reveal__target');
      btn.setAttribute('aria-expanded', !expanded);
      btn.classList.toggle('is-open');
      item.classList.toggle('is-open');
      target.toggleAttribute('hidden');
      
      if (!expanded) {
        target.animate([
          { transition: 'opacity', opacity: '0' },
          { opacity: '100' } // Keeps your original 100 value
        ], {
          duration: 250,
          easing: 'linear',
          iterations: 1
        });
      }
    });
  });


  const globalTriggers = document.querySelectorAll('.widget-expandcollapse:not([data-accordion-all-init])');
  globalTriggers.forEach(item => {
    item.setAttribute('data-accordion-all-init', 'true');
	
    let state = item.getAttribute('data-state') === 'true'; 

    item.addEventListener('click', () => {
      state = !state;
      item.innerText = (state ? "Close" : "Open") + ' All Accordions';
      const allAccordions = document.querySelectorAll('.widget-accordion');

      allAccordions.forEach(accordion => {
        let btn = accordion.querySelector('.js-reveal__trigger');
        let target = accordion.querySelector('.js-reveal__target');
        if (!btn || !target) return;

        if (state) {
          btn.setAttribute('aria-expanded', 'true');
          btn.classList.add('is-open');
          accordion.classList.add('is-open');
          item.classList.add('is-open');
          target.removeAttribute('hidden');
          target.animate([
            { transition: 'opacity', opacity: '0' },
            { opacity: '100' }
          ], {
            duration: 250,
            easing: 'linear',
            iterations: 1
          });
        } else {
          btn.setAttribute('aria-expanded', 'false');
          btn.classList.remove('is-open');
          accordion.classList.remove('is-open');
          item.classList.remove('is-open');
          target.setAttribute('hidden', '');
        }
      });
    });
  });



  function openAccordionFromHash() {
    if (!window.location.hash) return;
    
	
    let targetIdElement = document.querySelector(window.location.hash);
    if (!targetIdElement) return;


    let accordion = targetIdElement.closest('.widget-accordion');
    if (accordion) {
      accordion.classList.add('is-open');
      
      let trigger = accordion.querySelector('.accordion__trigger');
      if (trigger) {
        trigger.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
      }

      let targetContent = accordion.querySelector('.js-reveal__target.accordion__content');
      if (targetContent) {
        targetContent.removeAttribute('hidden');
      }

      targetIdElement.scrollIntoView();
    }
  }

  // Execute on initial page load and when hash changes
  openAccordionFromHash();
  window.addEventListener('hashchange', openAccordionFromHash);
});
