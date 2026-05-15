//==========================(كود لجعل صورة الشاشة الرئيسيه تتقلب تلقائي)=============================>>
// إنشاء كائن Swiper جديد وتطبيقه على العنصر الذي يحتوي على الفئة 'swiper-container'
var swiper = new Swiper('.swiper-container', {
  // تفعيل التكرار اللانهائي للشرائح
  loop: true,

  // إعدادات التشغيل التلقائي للشرائح
  autoplay: {
      // مدة التوقف بين كل صورة (3000 مللي ثانية = 3 ثواني)
      delay: 4000,
      // استمرار التشغيل التلقائي حتى بعد التفاعل مع الشرائح (مثل النقر أو السحب)
      disableOnInteraction: false,
  },

  // إعدادات الترقيم (النقاط) للشرائح
  pagination: {
      // العنصر الذي يحتوي على الترقيم
      el: '.swiper-pagination',
      // جعل الترقيم قابلاً للنقر للتنقل بين الشرائح
      clickable: true,
  },
});

//==========================(كود لجعل زر الموجب الذي في شريحة المنتج تتوسع ويظهر زر الزيادة وحقل الادخال وزر التنقيص)=============================>>

// احصل على جميع أزرار التوسيع
var expandButtons = document.querySelectorAll('.expand-quantity');

// أضف مستمع حدث لكل زر
for (var i = 0; i < expandButtons.length; i++) {
  expandButtons[i].addEventListener('click', function () {
    // احصل على الشريط المجاور للزر
    var quantityContainer = this.nextElementSibling;

    // أضف كلاس "active" لإظهار الشريط
    quantityContainer.classList.add('active');

    // أضف كلاس "hidden" لإخفاء الزر
    this.classList.add('hidden');
  });
}

//==============================(اكواد لزيادة وتنقيص الكمية)===========================>>
 // تحديث القيم عند التحميل
document.addEventListener('DOMContentLoaded', function() {
  // تحديد جميع المدخلات التي تحتوي على الكمية باستخدام فئة 'quantity'
  const quantityInputs = document.querySelectorAll('.quantity');
  // تحديد المدخلات المرتبطة بعدد الكمية باستخدام فئة 'count_quantity'
  const countQuantityInputs = document.querySelectorAll('.count_quantity');
  // تحديد المدخلات المخفية المرتبطة بالكمية باستخدام فئة 'hidden-quantity'
  const hiddenQuantityInputs = document.querySelectorAll('.hidden-quantity');

  // تنفيذ الكود على كل مدخل كمية
  quantityInputs.forEach((quantityInput, index) => {
      // تحديد المدخلات المرتبطة لكل كمية
      const countQuantityInput = countQuantityInputs[index];
      const hiddenQuantityInput = hiddenQuantityInputs[index];

      // تعيين القيمة الأولية من مدخل الكمية إلى المدخلات الأخرى (count و hidden)
      countQuantityInput.value = quantityInput.value;
      hiddenQuantityInput.value = quantityInput.value;

      // تحديث القيم عند تغيير الكمية في مدخل الكمية
      quantityInput.addEventListener('input', function() {
          // عندما يتغير المدخل، تحديث القيم الأخرى
          countQuantityInput.value = this.value;
          hiddenQuantityInput.value = this.value;
      });

      // زيادة الكمية عند الضغط على الزر "+"
      quantityInput.closest('.quantity-input-container').querySelector('.increase').addEventListener('click', function() {
          // زيادة قيمة مدخل الكمية
          quantityInput.stepUp();
          // تحديث القيم المرتبطة مع الكمية
          countQuantityInput.value = quantityInput.value;
          hiddenQuantityInput.value = quantityInput.value;
      });

      // تقليل الكمية عند الضغط على الزر "-"
      quantityInput.closest('.quantity-input-container').querySelector('.decrease').addEventListener('click', function() {
          // تقليل قيمة مدخل الكمية
          quantityInput.stepDown();
          // تحديث القيم المرتبطة مع الكمية
          countQuantityInput.value = quantityInput.value;
          hiddenQuantityInput.value = quantityInput.value;
      });
  });
});

/*//==========================(لجعل الشاشة تتحمل حتى يفتح الموقع)=============================>>

// عند تحميل الصفحة بالكامل، سيتم إخفاء شاشة التحميل وإظهار المحتوى
window.addEventListener("load", function () {
  const loading = document.getElementById("loading");
  const content = document.getElementById("content");

  loading.style.display = "none"; // إخفاء شاشة التحميل
  content.style.display = "block"; // إظهار المحتوى الرئيسي
});
*/
let menu_btu = document.querySelector(".bx-menu");
let con = document.querySelector(".contents");

menu_btu.onclick = function(){
  if(con.style.right == "-200px"){
    con.style.right = "0";
  }
  else{
    con.style.right = "-200px";
  }
}

let search_btu = document.querySelector(".bx-search-alt");
let filed_search = document.querySelector(".search_filed");
search_btu.onclick = function(){
  if(filed_search.style.top == "-50px"){
    filed_search.style.top = "12px";
  }
  else{
    filed_search.style.top = "-50px";
  }
}

