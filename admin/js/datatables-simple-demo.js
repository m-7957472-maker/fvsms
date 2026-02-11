window.addEventListener('DOMContentLoaded', event => {
    // Simple-DataTables
    // https://github.com/fiduswriter/Simple-DataTables/wiki

    const datatablesSimple = document.getElementById('datatablesSimple');
    if (datatablesSimple) {
        new simpleDatatables.DataTable(datatablesSimple, {
            labels: {
                placeholder: "Cari...",
                perPage: "{select} entri setiap halaman",
                noRows: "Tiada rekod ditemui",
                info: "Menunjukkan {start} hingga {end} dari {rows} entri"
            }
        });
    }
});
