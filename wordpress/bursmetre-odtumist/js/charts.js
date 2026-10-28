(function () {
  "use strict";

  const currency = new Intl.NumberFormat("tr-TR", { maximumFractionDigits: 0 });
  const formatCurrency = (value) => `₺${currency.format(value || 0)}`;
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function initialize(scope) {
    const root = scope && scope.querySelectorAll ? scope : document;
    root.querySelectorAll('[data-burs-avatar] img').forEach((img) => {
      if (img.dataset.bursFallbackReady) return;
      img.dataset.bursFallbackReady = '1';
      const fallback = () => { img.hidden = true; };
      img.addEventListener('load', () => { img.hidden = false; });
      img.addEventListener('error', fallback, { once: true });
      if (img.complete && img.currentSrc && img.naturalWidth === 0) fallback();
    });
    root.querySelectorAll(".burs-progress__inner[data-progress]").forEach((bar) => {
      const value = Number.parseFloat(bar.dataset.progress || "0");
      requestAnimationFrame(() => {
        bar.style.width = `${Number.isFinite(value) ? Math.min(Math.max(value, 0), 100) : 0}%`;
      });
    });

    const Chart = window.BursmetreChart;
    if (!Chart) return;
    root.querySelectorAll(".burs-dashboard canvas[data-chart-kind]").forEach((canvas) => {
      if (Chart.getChart(canvas)) return;
      let payload;
      try { payload = JSON.parse(canvas.dataset.chart || "{}"); } catch (error) { return; }
      if (!Array.isArray(payload.labels) || !payload.labels.length) return;
      const horizontal = canvas.dataset.chartKind === "department";
      const fontFamily = getComputedStyle(canvas.closest(".burs-dashboard")).fontFamily;
      const tickFont = { family: fontFamily, size: 12 };
      canvas.parentElement.style.height = `${Math.max(360, Math.min(540, payload.labels.length * 34))}px`;
      new Chart(canvas, {
        type: "bar",
        data: {
          labels: payload.labels,
          datasets: [{
            data: payload.values || [],
            backgroundColor: payload.colors || "#00529b",
            borderRadius: 8,
            borderSkipped: false,
            maxBarThickness: horizontal ? 18 : 32,
            originals: payload.originals || []
          }]
        },
        options: {
          indexAxis: horizontal ? "y" : "x",
          maintainAspectRatio: false,
          responsive: true,
          animation: reducedMotion ? false : undefined,
          color: "#475569",
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: "#0f172a",
              padding: 12,
              cornerRadius: 10,
              titleFont: { family: fontFamily, weight: "700" },
              bodyFont: { family: fontFamily },
              callbacks: {
                label: (context) => {
                  const amount = context.parsed[horizontal ? "x" : "y"];
                  const original = context.dataset.originals[context.dataIndex];
                  return `${formatCurrency(amount)}${original ? ` (${original})` : ""}`;
                }
              }
            }
          },
          layout: { padding: { top: 8, bottom: 12, left: 0, right: 8 } },
          scales: {
            x: {
              beginAtZero: horizontal,
              grid: { display: horizontal, color: "rgba(148, 163, 184, 0.2)" },
              ticks: horizontal
                ? { font: tickFont, callback: formatCurrency }
                : { font: tickFont, maxRotation: 48, minRotation: 48, autoSkip: false }
            },
            y: {
              beginAtZero: !horizontal,
              grid: { display: !horizontal, color: "rgba(148, 163, 184, 0.2)" },
              ticks: horizontal
                ? { font: { ...tickFont, weight: "600" }, color: "#0f172a" }
                : { font: tickFont, callback: formatCurrency }
            }
          }
        }
      });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => initialize(document));
  } else {
    initialize(document);
  }
  // Elementor can replace a shortcode widget without reloading its preview.
  if (window.jQuery) {
    window.jQuery(window).on("elementor/frontend/init", function () {
      if (window.elementorFrontend && window.elementorFrontend.hooks) {
        window.elementorFrontend.hooks.addAction("frontend/element_ready/shortcode.default", (scope) => initialize(scope[0]));
      }
    });
  }
})();
