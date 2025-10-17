<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<script>
    // notification
    function notification() {
        $.ajax({
            url: "",
            method: "GET",
            dataType: "JSON",
            success: function(response) {
                let html = '';
                response.data.data.forEach(notification => {
                    let createdAt = moment(notification.created_at).fromNow();
                    html += `
                        <a class="dropdown-item" onclick="readnotify('${notification.id}')">
                            <div class="notification-each d-flex">
                                <div class="me-3 notifyimg bg-primary brround">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                                        <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                                        <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="notification-label mb-1">${notification.data.message}</span>
                                    <span class="notification-subtext text-muted">${createdAt}</span>
                                </div>
                            </div>
                        </a>
                    `;
                });
                $("#notification").html(html);
            }
        });
    };
    notification();

    // Read Notification
    function readnotify(id) {
        $.ajax({
            url: "".replace(':id', id),
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': "{{csrf_token()}}",
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);

                } else {
                    toastr.error(response.message);
                }
                notification();
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                console.log('Error: ' + errorMessage);
            }
        });
    }

    // Mark All As Read
    function markAllAsRead() {
        $.ajax({
            url: "",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': "{{csrf_token()}}",
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
                notification();
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                console.log('Error: ' + errorMessage);
            }
        });
    }

    // Notification
    // document.addEventListener('DOMContentLoaded', function() {
    //     window.Echo.
    //         .listen('NewNotificationEvent', (event) => {
    //             toastr.success(event.data.message); // Ensure message is at the top level in the event data
    //             notification(); // Call your custom notification function
    //         });
    // });

    // Notification
    // document.addEventListener('DOMContentLoaded', function() {
    //     window.Echo.channel('web-notifications')
    //         .listen('WebNotificationEvent', (event) => {
    //             toastr.success(event.data.message); // Ensure message is at the top level in the event data
    //         });
    // });
</script>
