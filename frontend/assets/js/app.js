// Simple client-side router that loads HTML fragments from /frontend/views
(function(){
  const appEl = document.getElementById('app');
  const routes = {
    '#/login': 'login.html',
    '#/register': 'register.html',
    '#/dashboard': 'dashboard.html',
    '#/books': 'books.html',
    '#/authors': 'authors.html',
    '#/genres': 'genres.html',
    '#/users': 'users.html',
    '#/borrowings': 'borrowings.html',
    '#/profile': 'profile.html'
  };

  async function loadView(hash){
    const route = routes[hash] || routes['#/dashboard'];
    try {
      const res = await fetch(`./views/${route}`, { cache: 'no-cache' });
      const html = await res.text();
      appEl.innerHTML = html;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch(err){
      appEl.innerHTML = `<div class="alert alert-danger">Failed to load view.</div>`;
      console.error(err);
    }
  }

  function handleClick(e){
    const a = e.target.closest('a[href^="#/"]');
    if(a){
      e.preventDefault();
      const target = a.getAttribute('href');
      if(location.hash !== target){ location.hash = target; }
      else { loadView(target); }
    }
  }

  window.addEventListener('hashchange', () => loadView(location.hash));
  document.addEventListener('click', handleClick);

  if(!location.hash){ location.hash = '#/dashboard'; }
  loadView(location.hash);
})();
