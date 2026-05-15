function theChecker() {
    const confirmCheckbox = document.getElementById('confirm-order');
    const submitButton = document.getElementById('submit-button');

    if (confirmCheckbox.checked) {
        submitButton.disabled = false; // تفعيل الزر
        submitButton.style.backgroundColor = "#00b31e"; 

    } else {
        submitButton.disabled = true; // تعطيل الزر
    }
}