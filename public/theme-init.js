(function () {
  var saved = localStorage.getItem('theme');
  var dark = saved === 'dark';
  document.documentElement.classList.toggle('dark', dark);
})();
