
function changeValue(id, delta) {
  const input = document.getElementById(id);
  let value = parseInt(input.value) || 0;
  value += delta;
  if (value < 0) value = 0;
  input.value = value;
}

const dropdownItems = document.querySelectorAll(".dropdown-menu .dropdown-item");
const selectedContainer = document.getElementById("selected-options");

// لمنع التكرار
const selectedItems = new Set();

dropdownItems.forEach(item => {
  item.addEventListener("click", function (e) {
    e.preventDefault();
    const selectedValue = item.textContent.trim();

    if (!selectedItems.has(selectedValue)) {
      selectedItems.add(selectedValue);

      const badge = document.createElement("span");
      badge.className = "badge bg-primary me-2 mb-2";
      badge.innerText = selectedValue;

      selectedContainer.appendChild(badge);
    }
  });
});

const imageUpload = document.getElementById("imageUpload");
const preview = document.getElementById("preview");
const errorMsg = document.getElementById("errorMsg");

imageUpload.addEventListener("change", function () {
  const files = Array.from(imageUpload.files);
  
  preview.innerHTML = ""; // نفضي المعاينة

  // تحقق من الحد الأدنى
  if (files.length < 5) {
    errorMsg.classList.remove("d-none");
    return;
  } else {
    errorMsg.classList.add("d-none");
  }

  files.forEach(file => {
    const reader = new FileReader();
    reader.onload = function (e) {
      const img = document.createElement("img");
      img.src = e.target.result;
      img.className = "rounded border";
      img.style.width = "100px";
      img.style.height = "100px";
      img.style.objectFit = "cover";
      preview.appendChild(img);
    };
    reader.readAsDataURL(file);
  });
});

const buttons = document.querySelectorAll('.highlight-btn');

buttons.forEach(btn => {
btn.addEventListener('click', () => {
const selected = document.querySelectorAll('.highlight-btn.selected');
if (btn.classList.contains('selected')) {
  btn.classList.remove('selected');
} else {
  if (selected.length < 2) {
    btn.classList.add('selected');
  }
}
});
});
