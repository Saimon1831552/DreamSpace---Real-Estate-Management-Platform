<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DreamSpace - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link.active { background-color: #4f46e5; color: white; }
        .sidebar-link:hover:not(.active) { background-color: #eef2ff; }
        .loader { border: 4px solid #f3f3f3; border-radius: 50%; border-top: 4px solid #4f46e5; width: 40px; height: 40px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .detail-card { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        /* Modal styles */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
        .modal-content { background: white; padding: 2rem; border-radius: 0.5rem; max-width: 500px; width: 100%; }
    </style>
</head>
<body class="bg-gray-100">

    <div class="flex h-screen bg-gray-200">
        <!-- Sidebar -->
        <div id="sidebar" class="w-64 bg-white shadow-lg flex-shrink-0 transition-transform duration-300 ease-in-out">
            <div class="p-6 flex items-center justify-center border-b">
                <img src="https://img.icons8.com/fluency/48/home.png" class="w-8 h-8" alt="Logo" />
                <span class="ml-3 text-2xl font-bold text-red-600"><span class="text-black">D</span>reams<span class="text-black">S</span>pace</span>
            </div>
            <nav class="mt-6">
                <a href="#" class="sidebar-link flex items-center py-3 px-6 text-red-600 transition-colors duration-200 active" data-target="dashboard">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="mx-4">Dashboard</span>
                </a>
                <a href="#" class="sidebar-link flex items-center py-3 px-6 text-red-600" data-target="users">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197m0 0A10.99 10.99 0 002.45 12c0-2.433 1.04-4.636 2.744-6.212"></path></svg>
                    <span class="mx-4">Users</span>
                </a>
                <a href="#" class="sidebar-link flex items-center py-3 px-6 text-red-600" data-target="agents">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="mx-4">Agents</span>
                </a>
                <a href="#" class="sidebar-link flex items-center py-3 px-6 text-red-600" data-target="properties">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span class="mx-4">Properties</span>
                </a>
            </nav>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center p-6 bg-white border-b">
                <div class="flex items-center">
                    <button id="sidebar-toggle" class="text-gray-500 focus:outline-none lg:hidden">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6H20M4 12H20M4 18H11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <h1 id="page-title" class="text-2xl font-semibold text-gray-700 ml-4 lg:ml-0">Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, Admin!</span>
                    <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                        <img class="h-8 w-8 rounded-full object-cover" src="https://placehold.co/100x100/4F46E5/FFFFFF?text=A" alt="admin avatar">
                    </button>
                </div>
            </header>
            <main id="main-content" class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6"></main>
        </div>
    </div>
    
    <!-- Modal for editing -->
    <div id="edit-modal" class="modal-overlay hidden"></div>


    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const mainContent = document.getElementById('main-content');
        const pageTitle = document.getElementById('page-title');
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const editModal = document.getElementById('edit-modal');
        const API_URL = 'api.php';
        let propertyChart = null;

        const showLoader = () => { mainContent.innerHTML = '<div class="w-full h-full flex justify-center items-center"><div class="loader"></div></div>'; };
        const showError = (message) => { mainContent.innerHTML = `<div class="w-full text-center p-4 bg-red-100 text-red-700 rounded-lg">${message}</div>`; };

        // --- TEMPLATE GENERATORS ---
        const templates = {
            createDashboard: (stats) => `
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between"><div><p class="text-sm text-gray-500">Total Users</p><p class="text-3xl font-bold text-gray-800">${stats.totalUsers}</p></div><div class="bg-indigo-100 p-3 rounded-full"><svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197m0 0A10.99 10.99 0 002.45 12c0-2.433 1.04-4.636 2.744-6.212"></path></svg></div></div>
                    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between"><div><p class="text-sm text-gray-500">Total Agents</p><p class="text-3xl font-bold text-gray-800">${stats.totalAgents}</p></div><div class="bg-green-100 p-3 rounded-full"><svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div></div>
                    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between"><div><p class="text-sm text-gray-500">Total Properties</p><p class="text-3xl font-bold text-gray-800">${stats.totalProperties}</p></div><div class="bg-yellow-100 p-3 rounded-full"><svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></div></div>
                    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between"><div><p class="text-sm text-gray-500">Total Value</p><p class="text-3xl font-bold text-gray-800">$${parseFloat(stats.totalValue || 0).toLocaleString()}</p></div><div class="bg-red-100 p-3 rounded-full"><svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01"></path></svg></div></div>
                </div>
                <div class="mt-8 bg-white p-6 rounded-lg shadow-md"><h3 class="text-xl font-semibold text-gray-700">Property Listings Overview</h3><div class="h-96"><canvas id="propertyChart" class="mt-4"></canvas></div></div>`,
            createTable: (data, headers, rowRenderer) => {
                if (!data || data.length === 0) return `<div class="bg-white p-6 rounded-lg shadow-md w-full text-center text-gray-500">No data available.</div>`;
                return `<div class="bg-white p-6 rounded-lg shadow-md w-full overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            ${headers.map(h => `<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${h}</th>`).join('')}
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr></thead>
                        <tbody class="bg-white divide-y divide-gray-200">${data.map(rowRenderer).join('')}</tbody>
                    </table></div>`;
            },
            createUserDetails: (user, from) => `
                <div class="detail-card bg-white p-8 rounded-lg shadow-md w-full max-w-4xl mx-auto">
                    <div class="flex justify-between items-start mb-6">
                         <h2 class="text-2xl font-bold text-gray-800">User Details</h2>
                         <button class="back-btn text-indigo-600 hover:text-indigo-800 font-semibold" data-target="${from}">← Back to List</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><p class="text-sm text-gray-500">Full Name</p><p class="text-lg font-semibold">${user.name || 'N/A'}</p></div>
                        <div><p class="text-sm text-gray-500">Email</p><p class="text-lg font-semibold">${user.email || 'N/A'}</p></div>
                        <div><p class="text-sm text-gray-500">Gender</p><p class="text-lg font-semibold">${user.gender || 'N/A'}</p></div>
                        <div><p class="text-sm text-gray-500">Joined On</p><p class="text-lg font-semibold">${new Date(user.created_at).toLocaleString()}</p></div>
                    </div>
                    <div class="mt-8 pt-6 border-t flex items-center space-x-4">
                        <button class="edit-btn bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" data-id="${user.id}" data-type="user">Edit User</button>
                        <button class="delete-btn bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" data-id="${user.id}" data-type="user">Delete Account</button>
                    </div>
                </div>`,
            createAgentDetails: (agent, from) => `
                <div class="detail-card bg-white p-8 rounded-lg shadow-md w-full max-w-4xl mx-auto">
                    <div class="flex justify-between items-start mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Agent Details</h2>
                         <button class="back-btn text-indigo-600 hover:text-indigo-800 font-semibold" data-target="${from}">← Back to List</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-b pb-6 mb-6">
                        <div><p class="text-sm text-gray-500">Full Name</p><p class="text-lg font-semibold">${agent.name || 'N/A'}</p></div>
                        <div><p class="text-sm text-gray-500">Email</p><p class="text-lg font-semibold">${agent.email || 'N/A'}</p></div>
                        <div><p class="text-sm text-gray-500">Phone</p><p class="text-lg font-semibold">${agent.phone || 'N/A'}</p></div>
                        <div><p class="text-sm text-gray-500">Company</p><p class="text-lg font-semibold">${agent.company_name || 'N/A'}</p></div>
                        <div><p class="text-sm text-gray-500">Joined On</p><p class="text-lg font-semibold">${new Date(agent.created_at).toLocaleString()}</p></div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 mb-4">Properties by this Agent (${agent.properties.length})</h3>
                    <div class="overflow-y-auto max-h-64 border rounded-lg p-2">
                        ${agent.properties.length > 0 ? agent.properties.map(prop => `
                            <div class="flex justify-between items-center p-3 rounded-lg hover:bg-gray-50">
                                <div><p class="font-semibold">${prop.title}</p><p class="text-sm text-gray-500">${prop.location}</p></div>
                                <p class="font-semibold text-green-600">$${parseFloat(prop.price).toLocaleString()}</p>
                            </div>`).join('') : '<p class="text-gray-500 p-3">No properties listed by this agent.</p>'}
                    </div>
                    <div class="mt-8 pt-6 border-t flex items-center space-x-4">
                        <button class="edit-btn bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" data-id="${agent.id}" data-type="agent">Edit Agent</button>
                        <button class="delete-btn bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" data-id="${agent.id}" data-type="agent">Delete Account</button>
                    </div>
                </div>`,
            createPropertyDetails: (prop, from) => `
                <div class="detail-card bg-white p-8 rounded-lg shadow-md w-full max-w-4xl mx-auto">
                     <div class="flex justify-between items-start mb-6">
                         <h2 class="text-2xl font-bold text-gray-800">${prop.title}</h2>
                         <button class="back-btn text-indigo-600 hover:text-indigo-800 font-semibold" data-target="${from}">← Back to List</button>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 border-b pb-6 mb-6">
                         <div><p class="text-sm text-gray-500">Price</p><p class="text-2xl font-bold text-green-600">$${parseFloat(prop.price).toLocaleString()}</p></div>
                         <div><p class="text-sm text-gray-500">Location</p><p class="text-lg font-semibold">${prop.location}</p></div>
                         <div><p class="text-sm text-gray-500">Property Type</p><p class="text-lg font-semibold">${prop.property_type}</p></div>
                         <div><p class="text-sm text-gray-500">Bedrooms</p><p class="text-lg font-semibold">${prop.bedrooms}</p></div>
                         <div><p class="text-sm text-gray-500">Bathrooms</p><p class="text-lg font-semibold">${prop.bathrooms}</p></div>
                         <div><p class="text-sm text-gray-500">Area</p><p class="text-lg font-semibold">${prop.area_sqft} sqft</p></div>
                    </div>
                    <div class="mb-6"><p class="text-sm text-gray-500 mb-2">Description</p><p class="text-gray-700">${prop.description || 'No description provided.'}</p></div>
                    <div class="mb-6">
                        <p class="text-sm text-gray-500 mb-2">Listed By</p>
                        <div class="flex items-center bg-gray-50 p-3 rounded-lg"><p class="font-semibold">${prop.agent_name}</p><p class="text-sm text-gray-600 ml-4">${prop.agent_email}</p></div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-2">Images (${prop.images.length})</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            ${prop.images.length > 0 ? prop.images.map(img => `<img src="${img.image_path}" alt="Property Image" class="w-full h-32 object-cover rounded-lg shadow-sm" onerror="this.src='https://placehold.co/400x300/cccccc/ffffff?text=Image+Not+Found'">`).join('') : '<p class="text-gray-500 col-span-full">No images uploaded.</p>'}
                        </div>
                    </div>
                    <div class="mt-8 pt-6 border-t flex items-center space-x-4">
                        <button class="edit-btn bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" data-id="${prop.id}" data-type="property">Edit Property</button>
                        <button class="delete-btn bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" data-id="${prop.id}" data-type="property">Delete Property</button>
                    </div>
                </div>`,
            createPropertyEditForm: (prop) => `
                <div class="modal-content">
                    <h3 class="text-xl font-bold mb-4">Edit Property</h3>
                    <form id="edit-form" data-id="${prop.id}" data-type="property">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-gray-700">Title</label><input type="text" name="title" value="${prop.title || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                            <div><label class="block text-sm font-medium text-gray-700">Price</label><input type="number" step="0.01" name="price" value="${prop.price || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                            <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700">Location</label><input type="text" name="location" value="${prop.location || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                            <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700">Description</label><textarea name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">${prop.description || ''}</textarea></div>
                            <div><label class="block text-sm font-medium text-gray-700">Property Type</label><input type="text" name="property_type" value="${prop.property_type || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                            <div><label class="block text-sm font-medium text-gray-700">Area (sqft)</label><input type="number" step="0.01" name="area_sqft" value="${prop.area_sqft || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                            <div><label class="block text-sm font-medium text-gray-700">Bedrooms</label><input type="number" name="bedrooms" value="${prop.bedrooms || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                            <div><label class="block text-sm font-medium text-gray-700">Bathrooms</label><input type="number" name="bathrooms" value="${prop.bathrooms || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" class="cancel-edit bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Save Changes</button>
                        </div>
                    </form>
                </div>`,
            createUserEditForm: (user) => `
                <div class="modal-content">
                    <h3 class="text-xl font-bold mb-4">Edit User</h3>
                    <form id="edit-form" data-id="${user.id}" data-type="user">
                        <div class="space-y-4">
                            <div><label class="block text-sm font-medium text-gray-700">Full Name</label><input type="text" name="name" value="${user.name || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                            <div><label class="block text-sm font-medium text-gray-700">Email</label><input type="email" name="email" value="${user.email || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" class="cancel-edit bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Save Changes</button>
                        </div>
                    </form>
                </div>`,
            createAgentEditForm: (agent) => `
                <div class="modal-content">
                    <h3 class="text-xl font-bold mb-4">Edit Agent</h3>
                    <form id="edit-form" data-id="${agent.id}" data-type="agent">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-gray-700">Full Name</label><input type="text" name="name" value="${agent.name || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                            <div><label class="block text-sm font-medium text-gray-700">Email</label><input type="email" name="email" value="${agent.email || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                            <div><label class="block text-sm font-medium text-gray-700">Phone</label><input type="text" name="phone" value="${agent.phone || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                            <div><label class="block text-sm font-medium text-gray-700">Company Name</label><input type="text" name="company_name" value="${agent.company_name || ''}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" class="cancel-edit bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Save Changes</button>
                        </div>
                    </form>
                </div>`
        };

        // --- API CALLS ---
        const performAction = async (method, body) => {
            try {
                const options = {
                    method: method,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                };
                if (method !== 'DELETE') {
                    options.headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(body);
                } else {
                    options.body = new URLSearchParams(body).toString();
                }

                const response = await fetch(API_URL, options);
                const result = await response.json();
                
                if (!response.ok) throw new Error(result.error || `HTTP error! status: ${response.status}`);
                
                alert(result.message); // Simple feedback
                return true;
            } catch (error) {
                console.error(`Error performing action:`, error);
                alert(`Error: ${error.message}`);
                return false;
            }
        };

        // --- DATA FETCHING AND RENDERING ---
        const renderListView = async (viewTarget) => {
            showLoader();
            if (propertyChart) { propertyChart.destroy(); propertyChart = null; }

            const apiTarget = viewTarget === 'dashboard' ? 'stats' : viewTarget;

            try {
                const response = await fetch(`${API_URL}?data=${apiTarget}`);
                if (!response.ok) throw new Error(`Server responded with status: ${response.status}`);
                const data = await response.json();
                if (data.error) throw new Error(data.error);

                let html = '';
                switch(viewTarget) {
                    case 'dashboard':
                        html = templates.createDashboard(data.stats);
                        mainContent.innerHTML = html;
                        renderDashboardChart(data.stats.propertyTypes);
                        break;
                    case 'users':
                        html = templates.createTable(data.users, ['Name', 'Email', 'Joined On'], user => `<tr><td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${user.name}</td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.email}</td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(user.created_at).toLocaleDateString()}</td><td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><a href="#" class="view-btn text-indigo-600 hover:text-indigo-900" data-type="user" data-id="${user.id}">View</a></td></tr>`);
                        mainContent.innerHTML = html;
                        break;
                    case 'agents':
                        html = templates.createTable(data.agents, ['Name', 'Company', 'Email', 'Joined On'], agent => `<tr><td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${agent.name}</td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${agent.company_name || 'N/A'}</td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${agent.email}</td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(agent.created_at).toLocaleDateString()}</td><td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><a href="#" class="view-btn text-indigo-600 hover:text-indigo-900" data-type="agent" data-id="${agent.id}">View</a></td></tr>`);
                        mainContent.innerHTML = html;
                        break;
                    case 'properties':
                        html = templates.createTable(data.properties, ['Title', 'Location', 'Price', 'Agent', 'Listed On'], prop => `<tr><td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${prop.title}</td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${prop.location}</td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$${parseFloat(prop.price).toLocaleString()}</td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${prop.agent_name || 'Unknown'}</td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(prop.created_at).toLocaleDateString()}</td><td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><a href="#" class="view-btn text-indigo-600 hover:text-indigo-900" data-type="property" data-id="${prop.id}">View</a></td></tr>`);
                        mainContent.innerHTML = html;
                        break;
                }
                pageTitle.textContent = viewTarget.charAt(0).toUpperCase() + viewTarget.slice(1);
            } catch (error) {
                showError(`Failed to load data: ${error.message}`);
                console.error("Error fetching list view:", error);
            }
        };

        const renderDetailView = async (type, id, from) => {
            showLoader();
            try {
                const response = await fetch(`${API_URL}?data=${type}&id=${id}`);
                if (!response.ok) throw new Error(`Server responded with status: ${response.status}`);
                const data = await response.json();
                if (data.error) throw new Error(data.error);

                let html = '';
                switch(type) {
                    case 'user': html = templates.createUserDetails(data.user, from); break;
                    case 'agent': html = templates.createAgentDetails(data.agent, from); break;
                    case 'property': html = templates.createPropertyDetails(data.property, from); break;
                }
                mainContent.innerHTML = html;
                pageTitle.textContent = `${type.charAt(0).toUpperCase() + type.slice(1)} Details`;
            } catch (error) {
                showError(`Failed to load details: ${error.message}`);
                console.error("Error fetching detail view:", error);
            }
        };

        const renderDashboardChart = (propertyTypes) => {
            const ctx = document.getElementById('propertyChart')?.getContext('2d');
            if (!ctx) return;
            propertyChart = new Chart(ctx, { type: 'bar', data: { labels: propertyTypes.map(p => p.property_type), datasets: [{ label: '# of Properties', data: propertyTypes.map(p => p.count), backgroundColor: 'rgba(79, 70, 229, 0.6)', borderColor: 'rgba(79, 70, 229, 1)', borderWidth: 1 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } } });
        };

        // --- EVENT LISTENERS ---
        sidebarLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = link.dataset.target;
                sidebarLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                renderListView(target);
            });
        });

        mainContent.addEventListener('click', async (e) => {
            
            const viewBtn = e.target.closest('.view-btn');
            const backBtn = e.target.closest('.back-btn');
            const deleteBtn = e.target.closest('.delete-btn');
            const editBtn = e.target.closest('.edit-btn');

            if(viewBtn || backBtn || deleteBtn || editBtn) e.preventDefault();

            if (viewBtn) {
                const type = viewBtn.dataset.type;
                const id = viewBtn.dataset.id;
                const currentView = document.querySelector('.sidebar-link.active').dataset.target;
                renderDetailView(type, id, currentView);
            }

            if (backBtn) {
                const target = backBtn.dataset.target;
                renderListView(target);
            }

            if (deleteBtn) {
                const type = deleteBtn.dataset.type;
                const id = deleteBtn.dataset.id;
                if (confirm(`Are you sure you want to delete this ${type}? This action cannot be undone.`)) {
                    const success = await performAction('DELETE', { type, id });
                    if (success) {
                        renderListView(type + 's');
                    }
                }
            }
            
            if (editBtn) {
                const type = editBtn.dataset.type;
                const id = editBtn.dataset.id;
                const response = await fetch(`${API_URL}?data=${type}&id=${id}`);
                const data = await response.json();
                
                let formHtml = '';
                if (type === 'property') formHtml = templates.createPropertyEditForm(data.property);
                if (type === 'user') formHtml = templates.createUserEditForm(data.user);
                if (type === 'agent') formHtml = templates.createAgentEditForm(data.agent);

                if(formHtml) {
                    editModal.innerHTML = formHtml;
                    editModal.classList.remove('hidden');
                }
            }
        });
        
        editModal.addEventListener('click', async (e) => {
            if (e.target.matches('.cancel-edit') || e.target.closest('.modal-overlay') === e.target) {
                editModal.classList.add('hidden');
            }
            
            const form = e.target.closest('#edit-form');
            if (form && e.target.type === 'submit') {
                e.preventDefault();
                
                const id = form.dataset.id;
                const type = form.dataset.type;
                const formData = new FormData(form);
                const payload = Object.fromEntries(formData.entries());
                
                const success = await performAction('POST', { action: `update_${type}`, type: type, id: id, payload: payload });
                
                if (success) {
                    editModal.classList.add('hidden');
                    const currentView = document.querySelector('.sidebar-link.active').dataset.target;
                    renderDetailView(type, id, currentView);
                }
            }
        });

        sidebarToggle.addEventListener('click', () => { sidebar.classList.toggle('-translate-x-full'); });
        
        // --- INITIALIZATION ---
        renderListView('dashboard');
    });
    </script>
</body>
</html>
