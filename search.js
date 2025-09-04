// search.js
document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.getElementById('search-form');
    const propertyListings = document.getElementById('property-listings');
    const filterButtonsContainer = document.getElementById('filter-buttons');
    
    // Initialize Leaflet map
    let map = L.map('map').setView([23.8103, 90.4125], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    let markersLayer = new L.LayerGroup().addTo(map);

    // Function to fetch and display properties
    const fetchProperties = async (params = '') => {
        try {
            const response = await fetch(`search.php?${params}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const properties = await response.json();
            displayProperties(properties);
            updateMap(properties);
        } catch (error) {
            console.error("Could not fetch properties: ", error);
            propertyListings.innerHTML = '<p class="text-center col-span-full">Could not load properties. Please try again later.</p>';
        }
    };

    // Function to display properties in the grid
    const displayProperties = (properties) => {
        propertyListings.innerHTML = ''; // Clear existing listings
        if (properties.length === 0) {
            propertyListings.innerHTML = '<p class="text-center col-span-full text-gray-500">No properties found matching your criteria.</p>';
            return;
        }

        properties.forEach(property => {
            // Each card is now a clickable link to the property details page
            const cardLink = document.createElement('a');
            cardLink.href = `property-details.php?id=${property.id}`;
            cardLink.classList.add('card', 'bg-base-100', 'w-full', 'md:w-96', 'shadow-sm', 'border-2', 'border-gray-100', 'group', 'overflow-hidden', 'relative');
            
            cardLink.innerHTML = `
                <div class="card-body">
                    <h2 class="card-title text-xl font-bold group-hover:text-red-500 transition-colors">${escapeHTML(property.title)}</h2>
                    <p class="font-medium"><i class="fa-solid fa-location-dot"></i> ${escapeHTML(property.location)}</p>
                </div>
                <figure class="relative w-full h-64">
                    <img src="${escapeHTML(property.image) || './img/default-property.jpg'}" onerror="this.onerror=null;this.src='./img/default-property.jpg';" alt="${escapeHTML(property.title)}" class="w-full h-full object-cover transition duration-500 ease-in-out"/>
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition duration-500 ease-in-out"></div>
                </figure>
            `;
            propertyListings.appendChild(cardLink);
        });
    };

    // Function to update map with new markers
    const updateMap = (properties) => {
        markersLayer.clearLayers(); // Clear existing markers
        const bounds = [];

        properties.forEach(p => {
            if (p.latitude && p.longitude) {
                const latLng = [p.latitude, p.longitude];
                const popupContent = `
                    <b>${escapeHTML(p.title)}</b><br>
                    Location: ${escapeHTML(p.location)}<br>
                    Price: $${Number(p.price).toLocaleString()}<br>
                    ${p.image ? `<a href="property-details.php?id=${p.id}" target="_blank"><img src="${escapeHTML(p.image)}" width="100" alt="Property Image"></a>` : ''}
                `;
                L.marker(latLng).addTo(markersLayer).bindPopup(popupContent);
                bounds.push(latLng);
            }
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        } else {
            // If no properties have coordinates, reset to default view
            map.setView([23.8103, 90.4125], 7);
        }
    };

    // Handle search form submission
    searchForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(searchForm);
        const params = new URLSearchParams(formData).toString();
        fetchProperties(params);
    });

    // Handle filter button clicks
    filterButtonsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('filter-btn')) {
            // Style the active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('bg-red-700', 'text-white');
                btn.classList.add('bg-gray-200', 'text-black');
            });
            e.target.classList.add('bg-red-700', 'text-white');
            e.target.classList.remove('bg-gray-200', 'text-black');

            const propertyType = e.target.dataset.type;
            document.getElementById('property-type').value = propertyType === 'All' ? '' : propertyType;
            // Trigger a search with the new type
            const formData = new FormData(searchForm);
            const params = new URLSearchParams(formData).toString();
            fetchProperties(params);
        }
    });

    // Utility to escape HTML to prevent XSS
    const escapeHTML = (str) => {
        if (str === null || str === undefined) return '';
        return str.toString().replace(/[&<>"']/g, function(match) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[match];
        });
    };

    // Initial load of all properties
    fetchProperties();
});
