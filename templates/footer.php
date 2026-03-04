<script>
document.addEventListener("DOMContentLoaded", function () {

    const gradeLevel = document.querySelector("select[name='grade_level']");
    const gradeNumber = document.getElementById("gradeNumber");

    if (!gradeLevel || !gradeNumber) return;

    function filterGradeNumbers() {
        const level = gradeLevel.value;

        Array.from(gradeNumber.options).forEach(option => {
            if (option.value === "") return;

            const number = parseInt(option.value);
            option.disabled = false;

            if (level === "Elementary" && (number < 1 || number > 6)) {
                option.disabled = true;
            }

            if (level === "Junior High" && (number < 7 || number > 10)) {
                option.disabled = true;
            }

            if (level === "Senior High" && (number < 11 || number > 12)) {
                option.disabled = true;
            }
        });

        gradeNumber.value = "";
    }

    gradeLevel.addEventListener("change", filterGradeNumbers);
});



document.addEventListener("DOMContentLoaded", function () {

    const gradeLevel = document.querySelector("select[name='grade_level']");
    const strandField = document.getElementById("strandField");
    const strandSelect = document.querySelector("select[name='strand_id']");

    if (!gradeLevel || !strandField) return;

    function toggleStrand() {
        if (gradeLevel.value === "Senior High") {
            strandField.classList.remove("d-none");
            strandSelect.setAttribute("required", "required");
        } else {
            strandField.classList.add("d-none");
            strandSelect.removeAttribute("required");
            strandSelect.value = "";
        }
    }

    gradeLevel.addEventListener("change", toggleStrand);
    toggleStrand();
});
</script>

</body>
</html>
