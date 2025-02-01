<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@if(session('success'))
<script>
    $(document).ready(function() {
        toastr.success("{{ session('success') }}");
    });
</script>
@endif
@if (session('error'))
<script>
    $(document).ready(function() {
        toastr.error("{{ session('error') }}");
    });
</script>
@endif
@if (session('warning'))
<script>
    $(document).ready(function() {
        toastr.warning("{{ session('warning') }}");
    });
</script>
@endif


