const data_table = document.getElementById("data");
const form = document.querySelector("form");
const usersSelect = document.getElementById("user");
const header = document.querySelector("#data h2");
const table = document.querySelector("table");

form.addEventListener("submit", (e) => {
  e.preventDefault();
  data_table.style.display = "block";

  const userId = Object.fromEntries(new FormData(form));

  let tableHeaderRowCount = 1;
  let rowCount = table.rows.length;
  let selectedText = usersSelect.options[usersSelect.selectedIndex].text;
  header.innerHTML = selectedText;

  for (var i = tableHeaderRowCount; i < rowCount; i++) {
    table.deleteRow(tableHeaderRowCount);
  }

  getData(userId.user);
});

async function getData(userId) {
  const url = "data.php?user=" + userId;
  const table = document.querySelector("table");

  try {
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`Response status: ${response.status}`);
    }

    const data = await response.json();

    for (const property in data) {
      row = table.insertRow();
      cell = row.insertCell();
      cell.textContent = Intl.DateTimeFormat("en", { month: "long" }).format(
        new Date(property)
      );
      cell = row.insertCell();
      cell.textContent = data[property].total;
      cell = row.insertCell();
      cell.textContent = data[property].count;
    }
  } catch (error) {
    console.error(error.message);
  }
}
