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
      
      // Extract scripts using regex before parsing
      const scriptRegex = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi;
      const scripts = [];
      let scriptMatch;
      let htmlWithoutScripts = html;
      
      // Extract all scripts
      while ((scriptMatch = scriptRegex.exec(html)) !== null) {
        scripts.push(scriptMatch[0]);
        htmlWithoutScripts = htmlWithoutScripts.replace(scriptMatch[0], '');
      }
      
      // Insert HTML without scripts
      appEl.innerHTML = htmlWithoutScripts;
      
      // Execute scripts after DOM is updated
      scripts.forEach(scriptHtml => {
        // Parse script tag
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = scriptHtml;
        const scriptEl = tempDiv.querySelector('script');
        
        if (scriptEl) {
          if (scriptEl.src) {
            const newScript = document.createElement('script');
            newScript.src = scriptEl.src;
            newScript.async = false;
            document.head.appendChild(newScript);
          } else {
            // For inline scripts, execute after DOM is ready
            const scriptContent = scriptEl.textContent;
            if (scriptContent.trim()) {
              // Use requestAnimationFrame to ensure DOM is ready
              requestAnimationFrame(() => {
                try {
                  eval(scriptContent);
                } catch(e) {
                  console.error('Script execution error:', e);
                }
              });
            }
          }
        }
      });
      
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
