//========================={شروط صفحة تغيير كلمة السر}=========================>>

document.getElementById("update").onsubmit = function(){

    let email = document.getElementById("email").value;//يقوم بجلب الايميل المدخل وإسنادة للمتغير
    let emiR = /[\w.]+@(gmail|yahoo|hotmail)+.(com|org|info|net)$/is;//صيغة الايميل الذي يجيب ان يكون الايميل مكتب عليها
    let va = emiR.test(email);//مقارنة صيغة الايميل المدخل مع صيغة الايميل المحدد
    
    //==================================================>>
    let pass = document.getElementById("new-password").value;//يقوم بجلب كلمة السر  المدخل وإسنادة للمتغير
    /*let passR = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{3,}$/is;//صيغة كلمة السر الذي يجيب ان تكون علية حيث يجب ان تحتوي على 6 ارقام على الاقل وحروف ورموز
    let password = passR.test(pass);//مقارنة*/
    
    let confirm_password = document.getElementById("confirm-password").value;//يقوم بجلب كلمة السر  المدخل وإسنادة للمتغير
    let phone_number = document.getElementById("phone_number").value;
    if (email === "") {
        alert("يجب عليك إدخال البريد الالكتروني");
        return false;
    }
    
    if (va === false) {
        alert("يجب عليك إدخال البريد الالكتروني بشكل صحيح");
        return false;
    }
    if (phone_number === "") {
        alert("يجب ملء حقل رقم الهاتف");
        return false;
    }
    
    /*if(password === false){
        alert("يجب ادخال كلمة مرور قوية بحيث تحتوي على رموز وحروف وأرقام انجليزي");
        return false;
    }*/
    
    if(pass === ""){
        alert("يجب ملء حقل كلمة السر الجديدة بكلمة سر قوية");
        return false;
    }
    
    if(confirm_password === "" || confirm_password != pass){
        alert("يجب ان يكون تأكيد كلمة السر تطابق كلمة السر الجديدة");
        return false;
    }
    
    return true ;
    }
    
    

// دالة تبديل رؤية كلمة السر
function show_new_password() {
const newPasswordInput = document.getElementById('new-password');
const eyes = document.querySelector(".toggle-password");

if (newPasswordInput.type === 'password') {
    newPasswordInput.type = 'text';
    eyes.innerHTML = "<i class='bx bxs-show'></i>"; // أيقونة العين المفتوحة
} else {
    newPasswordInput.type = 'password';
    eyes.innerHTML ="<i class='bx bxs-hide'></i>"; // أيقونة العين المغلقة
}
}

// دالة تبديل رؤية كلمة السر
function show_password() {
    const password = document.querySelector('#password');
    const eyes = document.querySelector(".toggle-password");
    
    if (password.type === 'password') {
        password.type = 'text';
        eyes.innerHTML = "<i class='bx bxs-show'></i>"; // أيقونة العين المفتوحة
    } else {
        password.type = 'password';
        eyes.innerHTML ="<i class='bx bxs-hide'></i>"; // أيقونة العين المغلقة
    }
 }