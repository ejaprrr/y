document.addEventListener("DOMContentLoaded", function () {
  const multiSelects = document.querySelectorAll("select[multiple]");

  multiSelects.forEach((select) => {
    // Function to check if scrolling is needed
    function checkOverflow() {
      if (select.scrollHeight > select.clientHeight) {
        select.classList.add("has-overflow");
      } else {
        select.classList.remove("has-overflow");
      }
    }

    // Check initially
    checkOverflow();

    // Check after content changes (selections, options added/removed)
    select.addEventListener("change", checkOverflow);

    // Fix wheel scrolling
    select.addEventListener("wheel", function (e) {
      if (this.scrollHeight > this.clientHeight) {
        e.preventDefault();
        this.scrollTop += e.deltaY > 0 ? 30 : -30;
      }
    });
  });
});
