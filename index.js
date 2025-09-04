

const endings = ["Dream Home", "Real Estate", "Perfect Home"];
let i = 0;
const el = document.getElementById("changing-text");

setInterval(() => {
  el.classList.replace("opacity-100", "opacity-0");

  setTimeout(() => {
    i = (i + 1) % endings.length;
    el.textContent = endings[i];
    el.classList.replace("opacity-0", "opacity-100");
  }, 500);
}, 2500);


function switchTab(tab) {
  const rentBtn = document.getElementById('tab-rent');
  const saleBtn = document.getElementById('tab-sale');
  const tabText = document.getElementById('selected-tab');

  if (tab === 'rent') {
    rentBtn.classList.add('text-gray-900', 'bg-white', 'border-b-4', 'border-red-500');
    rentBtn.classList.remove('text-gray-500', 'bg-gray-100');

    saleBtn.classList.remove('text-gray-900', 'bg-white', 'border-b-4', 'border-red-500');
    saleBtn.classList.add('text-gray-500', 'bg-gray-100');

    tabText.innerHTML = 'Currently Showing: <strong>FOR RENT</strong>';
  } else {
    saleBtn.classList.add('text-gray-900', 'bg-white', 'border-b-4', 'border-red-500');
    saleBtn.classList.remove('text-gray-500', 'bg-gray-100');

    rentBtn.classList.remove('text-gray-900', 'bg-white', 'border-b-4', 'border-red-500');
    rentBtn.classList.add('text-gray-500', 'bg-gray-100');

    tabText.innerHTML = 'Currently Showing: <strong>FOR SALE</strong>';
  }
}


document.addEventListener("DOMContentLoaded", () => {
  const menuToggle = document.getElementById("menuToggle");
  const navbarMenu = document.getElementById("navbarMenu");

  menuToggle.addEventListener("click", () => {
    navbarMenu.classList.toggle("hidden");
    navbarMenu.classList.toggle("flex");
    navbarMenu.classList.toggle("flex-col");
    navbarMenu.classList.toggle("gap-4");
    navbarMenu.classList.toggle("bg-white");
    navbarMenu.classList.toggle("p-4");
    navbarMenu.classList.toggle("rounded-b-lg");
    navbarMenu.classList.toggle("shadow-md");
  });
});