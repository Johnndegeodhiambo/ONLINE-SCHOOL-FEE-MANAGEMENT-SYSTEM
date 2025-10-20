// public/assets/js/charts.js

document.addEventListener("DOMContentLoaded", function () {
  const ctx = document.getElementById("feesChart");
  if (!ctx) return; // only run if the canvas exists

  const feesChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
      datasets: [
        {
          label: "Fees Collected (KSh)",
          data: [120000, 150000, 100000, 180000, 200000, 170000],
          backgroundColor: "rgba(54, 162, 235, 0.6)",
          borderColor: "rgba(54, 162, 235, 1)",
          borderWidth: 1,
        },
        {
          label: "Outstanding (KSh)",
          data: [50000, 30000, 70000, 20000, 15000, 40000],
          backgroundColor: "rgba(255, 99, 132, 0.6)",
          borderColor: "rgba(255, 99, 132, 1)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: "top" },
        title: {
          display: true,
          text: "School Fee Collections Overview",
        },
      },
      scales: {
        y: { beginAtZero: true },
      },
    },
  });
});
