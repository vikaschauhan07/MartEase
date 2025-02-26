<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Deletion</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  
  <div class="container mx-auto p-6 max-w-lg bg-white shadow-lg rounded-lg">
    <h2 class="text-2xl font-bold mb-4 text-center">Request Account Deletion</h2>
    
    <input type="email" id="email" placeholder="Enter your email" class="w-full p-2 border border-gray-300 rounded mb-4" oninput="validateEmail()">
    
    <button id="deleteButton" onclick="requestDeletion()" class="bg-red-500 text-white px-4 py-2 rounded w-full hover:bg-red-700 disabled:bg-gray-400" disabled>
      Delete My Account
    </button>
  </div>
  
  <!-- Confirmation Dialog -->
  <div id="confirmDialog" class="hidden fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
    <div class="bg-white p-6 rounded-lg shadow-lg">
      <p class="mb-4">Are you sure you want to delete your account? This action cannot be undone.</p>
      <input type="email" id="confirmEmail" placeholder="Confirm your email" class="w-full p-2 border border-gray-300 rounded mb-4" oninput="validateConfirmEmail()">
      
      <p class="mb-2">Why are you leaving?</p>
      <div class="mb-4">
        <label class="block"><input type="radio" name="reason" value="Not satisfied" onclick="validateReason()"> Not satisfied with the service</label>
        <label class="block"><input type="radio" name="reason" value="Privacy concerns" onclick="validateReason()"> Privacy concerns</label>
        <label class="block"><input type="radio" name="reason" value="Found an alternative" onclick="validateReason()"> Found an alternative</label>
        <label class="block"><input type="radio" name="reason" value="Other" onclick="enableTextarea()"> Other (please specify below)</label>
      </div>
      
      <textarea id="reason" placeholder="Please let us know why you are leaving" class="w-full p-2 border border-gray-300 rounded mb-4 hidden" oninput="validateReason()"></textarea>
      
      <div class="flex justify-between">
        <button id="confirmButton" onclick="confirmDeletion()" class="bg-red-600 text-white px-4 py-2 rounded disabled:bg-gray-400" disabled>Confirm</button>
        <button onclick="cancelDeletion()" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
      </div>
    </div>
  </div>
  
  <script>
    function validateEmail() {
      const email = document.getElementById('email').value;
      const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      document.getElementById('deleteButton').disabled = !isValid;
    }
    
    function requestDeletion() {
      document.getElementById('confirmDialog').classList.remove('hidden');
    }
    
    function validateConfirmEmail() {
      const email = document.getElementById('email').value;
      const confirmEmail = document.getElementById('confirmEmail').value;
      const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(confirmEmail);
      validateReason();
      document.getElementById('confirmButton').disabled = email !== confirmEmail || !isValid;
    }
    
    function validateReason() {
      const selectedReason = document.querySelector('input[name="reason"]:checked');
      const reasonTextarea = document.getElementById('reason');
      const isOtherSelected = selectedReason && selectedReason.value === "Other";
      
      reasonTextarea.classList.toggle('hidden', !isOtherSelected);
      
      const reasonValid = selectedReason && (isOtherSelected ? reasonTextarea.value.trim() !== "" : true);
      const confirmEmail = document.getElementById('confirmEmail').value;
      const isEmailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(confirmEmail);
      document.getElementById('confirmButton').disabled = !reasonValid || !isEmailValid;
    }
    
    function enableTextarea() {
      document.getElementById('reason').classList.remove('hidden');
      validateReason();
    }
    
    function confirmDeletion() {
      toastr.success('Account deleted successfully!');
      document.getElementById('confirmDialog').classList.add('hidden');
    }
    
    function cancelDeletion() {
      document.getElementById('confirmDialog').classList.add('hidden');
    }
  </script>
  
</body>
</html>
