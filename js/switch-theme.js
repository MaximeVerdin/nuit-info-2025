(() => {
  let prefix = "";
  if (window.location.pathname.includes("/html/")) {
    prefix = "../";
  } else if (window.location.pathname.includes("/php/")) {
    prefix = "../";
  }

  const THEMES = {
    "modern-gradient": {
      css: prefix + "css/modernstyle.css",
      label: "✨ Moderne",
    },
    retro: {
      css: prefix + "css/gamingcss.css",
      label: "🎮 Retro",
    },
  };

  const STORAGE_KEY = "nird-theme";

  function createThemeSwitcher() {
    const switcher = document.createElement("div");
    switcher.id = "theme-switcher";
    switcher.style.cssText = `
      position: fixed;
      top: 20px;
      left: 20px;
      z-index: 99999 !important;
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      max-width: 300px;
    `;

    Object.entries(THEMES).forEach(([key, theme]) => {
      const btn = document.createElement("button");
      btn.textContent = theme.label;
      btn.dataset.theme = key;
      btn.className = "theme-btn";

      btn.style.cssText = `
        padding: 10px 15px;
        background: #222;
        border: 2px solid #fff;
        border-radius: 8px;
        color: white;
        cursor: pointer;
        font-weight: bold;
        z-index: 99999 !important;
      `;

      btn.addEventListener("click", () => {
        setTheme(key);
        updateActiveButton(key);
      });

      switcher.appendChild(btn);
    });

    document.body.appendChild(switcher);
  }

  function setTheme(name) {
    const theme = THEMES[name];
    if (!theme) return;

    document
      .querySelectorAll("link[data-theme-css]")
      .forEach((l) => l.remove());

    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = theme.css;
    link.dataset.themeCss = "true";
    document.head.appendChild(link);

    localStorage.setItem(STORAGE_KEY, name);
  }

  function updateActiveButton(name) {
    document.querySelectorAll(".theme-btn").forEach((btn) => {
      btn.style.opacity = btn.dataset.theme === name ? "1" : "0.5";
    });
  }

  function init() {
    const saved = localStorage.getItem(STORAGE_KEY) || "modern-gradient";
    setTheme(saved);
    createThemeSwitcher();
    updateActiveButton(saved);
  }

  document.addEventListener("DOMContentLoaded", init);
})();
