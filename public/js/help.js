document.addEventListener('DOMContentLoaded', () => {
  const faqItems = Array.from(document.querySelectorAll('.faq-item'));
  const searchInput = document.getElementById('faq-search');

  // toggle odpowiedzi po kliknięciu pytania
  faqItems.forEach(item => {
    item.querySelector('.question').addEventListener('click', () => {
      const ans = item.querySelector('.answer');
      ans.style.display = ans.style.display === 'block' ? 'none' : 'block';
    });
  });

  // filtrowanie pytań
  searchInput.addEventListener('input', () => {
    const term = searchInput.value.toLowerCase();
    faqItems.forEach(item => {
      const q = item.querySelector('.question').textContent.toLowerCase();
      item.style.display = q.includes(term) ? 'block' : 'none';
    });
  });
});
