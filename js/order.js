$(document).ready(function () {
    fetchOrders();

    // Event delegation for dynamically created buttons
    $(document).on("click", ".view-btn", function () {
        let row = $(this).closest("tr");
        let name = row.find("td:nth-child(1)").text().trim();
        let order = row.find("td:nth-child(2)").text().trim();
        viewOrder(name, order);
    });

    $(document).on("click", ".confirm-btn", function () {
        let id = $(this).attr("data-id"); // Get order ID
        confirmOrder(id, this);
    });

    $(document).on("click", ".delete-btn", function () {
        let id = $(this).attr("data-id"); // Get order ID
        if (id) {
            deleteOrder(id, this);
        } else {
            console.error("Order ID is undefined.");
        }
    });

    // Close modal when clicking outside the modal content
    $(document).on("click", "#orderModal", function (e) {
        if ($(e.target).hasClass("modal")) {
            closeModal();
        }
    });

    // Close modal on ESC key press
    $(document).on("keydown", function (e) {
        if (e.key === "Escape") {
            closeModal();
        }
    });

    // Ensure modal is hidden initially
    $("#orderModal").hide();
});

// Fetch Orders from API
function fetchOrders() {
    $.getJSON("/WEB-SM/api/place_order.php?action=fetch", function (data) {
        console.log("Server Response:", data); // Log the server response
        let tableBody = $(".order-table tbody");
        tableBody.empty(); // Clear existing rows

        if (data && data.success && data.data) {
            if (data.data.length > 0) {
                data.data.forEach(order => {
                    // Ensure total_price is a number
                    const totalPrice = parseFloat(order.total_price);
                    if (isNaN(totalPrice)) {
                        console.error("Invalid total_price for order:", order);
                        return; // Skip this order if total_price is invalid
                    }

                    let row = `<tr>
                        <td>${order.customer_name}</td>
                        <td>${formatOrderDetails(order.order_details)}</td> <!-- Format order details -->
                        <td>${order.status}</td>
                        <td>₱${totalPrice.toFixed(2)}</td> <!-- Display total price -->
                        <td>
                            <button class="view-btn">View</button>
                            <button class="confirm-btn" data-id="${order.id}">Confirm</button>
                            <button class="delete-btn" data-id="${order.id}">Delete</button>
                        </td>
                    </tr>`;
                    tableBody.append(row);
                });
            } else {
                tableBody.append("<tr><td colspan='5'>No orders found.</td></tr>");
            }
        } else {
            console.error("Invalid server response:", data);
            tableBody.append("<tr><td colspan='5'>Error fetching orders.</td></tr>");
        }
    }).fail(function (xhr, status, error) {
        console.error("Error fetching orders:", status, error);
        $(".order-table tbody").append("<tr><td colspan='5'>Error fetching orders.</td></tr>");
    });
}

// Helper function to format order details
function formatOrderDetails(orderDetails) {
    try {
        const items = JSON.parse(orderDetails);
        return items.map(item => `${item.name} (x${item.quantity})`).join(", ");
    } catch (e) {
        console.error("Error parsing order details:", e);
        return "Invalid order details";
    }
}

// View Order Modal
function viewOrder(name, order) {
    $("#orderDetails").text(`${name} ordered: ${order}`);
    $("#orderModal").fadeIn(200); // Use fadeIn for smooth appearance
}

// Close Modal
function closeModal() {
    $("#orderModal").fadeOut(200); // Use fadeOut for smooth disappearance
}

// Confirm Order (Remove Row on Success)
function confirmOrder(id, button) {
    console.log("Confirming Order ID:", id);

    $.post("/WEB-SM/api/place_order.php", { action: "confirm", id: id }, function (response) {
        console.log("Response from Server:", response);

        if (response.success) {
            $(button).closest("tr").fadeOut(300, function () {
                $(this).remove();
            });
        } else {
            alert("Error confirming order.");
        }
    }, "json").fail(function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
    });
}

function deleteOrder(id, button) {
    console.log("Deleting Order ID:", id);

    if (confirm("Are you sure you want to delete this order?")) {
        $.post("/WEB-SM/api/place_order.php", { action: "delete", id: id }, function (response) {
            console.log("Response from Server:", response);

            if (response.success) {
                $(button).closest("tr").fadeOut(300, function () {
                    $(this).remove();
                });
            } else {
                alert("Error deleting order: " + response.error);
            }
        }, "json").fail(function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
        });
    }
}
