document.addEventListener("DOMContentLoaded", function () {
    // Get the canvas elements
    const ordersCanvas = document.getElementById('myChart');
    const customersCanvas = document.getElementById('customerChart');

    if (!ordersCanvas || !customersCanvas) {
        console.error('One or more chart elements are missing in the HTML.');
        return; // Exit if elements are missing
    }

    const ctxOrders = ordersCanvas.getContext('2d');
    const ctxCustomers = customersCanvas.getContext('2d');

    // Initialize the Pie Chart for Total Products and Total Orders
    const myChart = new Chart(ctxOrders, {
        type: 'pie',
        data: {
            labels: ['Total Products', 'Total Orders'],
            datasets: [{
                label: 'Total Products and Orders',
                data: [0, 0], // Default values
                backgroundColor: ['rgba(75, 192, 192, 0.5)', 'rgba(255, 99, 132, 0.5)'],
                borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Total Products and Orders',
                    font: { size: 16 }
                }
            }
        }
    });

    // Initialize the Orders Per Customer Chart (Bar Chart)
    const customerChart = new Chart(ctxCustomers, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Orders per Customer',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, stepSize: 1 } }
        }
    });

    // Fetch data in parallel and update charts
    Promise.all([
        fetchData('/WEB-SM/api/fetch_products.php?action=getTotalProducts', 'totalProducts', 0, myChart),
        fetchData('/WEB-SM/api/fetch_orders.php?action=getTotalOrders', 'total_orders', 1, myChart),
        fetchData('/WEB-SM/api/fetch_orders.php?action=customerOrderStats', null, null, customerChart)        
    ]).then(() => {
        myChart.update();
        customerChart.update();
    }).catch(error => console.error("Error updating charts:", error));
});

function fetchData(url, key, index, chart) {
    return fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log(`Fetched data from ${url}:`, data); // Debugging

            if (typeof data !== 'object') {
                console.error(`Invalid JSON response from ${url}:`, data);
                return;
            }

            // Handling "customerOrderStats" (bar chart)
            if (!key && chart.config.type === 'bar') {
                if (Array.isArray(data.labels) && Array.isArray(data.values)) {
                    chart.data.labels = data.labels;
                    chart.data.datasets[0].data = data.values;
                    chart.update();
                } else {
                    console.error(`Invalid format for customer order stats from ${url}:`, data);
                }
                return;
            }

            // Handling "getTotalOrders" and "getTotalProducts" (pie chart)
            if (key && data.hasOwnProperty(key)) {
                let value = Number(data[key]); // Ensure it's a number
                if (!isNaN(value)) {
                    chart.data.datasets[0].data[index] = value;
                    chart.update();
                } else {
                    console.error(`Invalid number format for ${key} from ${url}:`, data[key]);
                }
            } else {
                console.error(`Invalid response format from ${url}. Response received:`, JSON.stringify(data));
            }
        })
        .catch(error => console.error(`Error fetching data from ${url}:`, error));
}