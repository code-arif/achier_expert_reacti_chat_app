<script>
    function newUserCount() {
        $.ajax({
            url: "",
            method: "GET",
            dataType: "JSON",
            success: function(response) {
                $('#newUserCount').html(response.data);
                $('#userCount').html(response.data);
            }
        });
    };
    newUserCount();
</script>
