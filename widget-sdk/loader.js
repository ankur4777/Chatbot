(function () {

    const currentScript = document.currentScript;

    if (!currentScript) {
        console.error("Chatbot Loader: Script tag not found.");
        return;
    }

    const widgetKey = currentScript.dataset.widgetKey;

    if (!widgetKey) {
        console.error("Chatbot Loader: data-widget-key is missing.");
        return;
    }

    window.ChatbotConfig = {
        widgetKey: widgetKey
    };

    const baseUrl = currentScript.src.replace("/loader.js", "");

    // Google Font
    const font = document.createElement("link");
    font.rel = "stylesheet";
    font.href =
        "https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap";
    document.head.appendChild(font);

    // Bootstrap Icons
    const icons = document.createElement("link");
    icons.rel = "stylesheet";
    icons.href =
        "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css";
    document.head.appendChild(icons);

    // Widget CSS
    const css = document.createElement("link");
    css.rel = "stylesheet";
    css.href = `${baseUrl}/chatbot.css`;
    document.head.appendChild(css);

    // Widget JS
    const script = document.createElement("script");
    script.src = `${baseUrl}/chatbot.js`;
    script.defer = true;
    document.head.appendChild(script);

})();