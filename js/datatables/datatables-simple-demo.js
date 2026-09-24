window.addEventListener('DOMContentLoaded', event => {
    const datatablesSimple = document.getElementById('datatablesSimple');
    if (datatablesSimple) {
        new simpleDatatables.DataTable(datatablesSimple, {
            // 1. Set jumlah data per halaman jadi 5
            perPage: 5, 
            // 2. Opsi pilihan dropdown yang muncul
            perPageSelect: [5, 10, 25, "All"], 
            
            // Catatan: Library ini TIDAK mendukung tombol PDF/Excel secara bawaan.
            // Hanya mendukung sort dan search dasar.
        });
    }
});

window.addEventListener('DOMContentLoaded', event => {
    // Simple-DataTables
    // https://github.com/fiduswriter/Simple-DataTables/wiki

    const datatablesSimple = document.getElementById('datatablesInventaris');
    if (datatablesSimple) {
        new simpleDatatables.DataTable(datatablesSimple);
    }
});

window.addEventListener('DOMContentLoaded', event => {
    // Simple-DataTables
    // https://github.com/fiduswriter/Simple-DataTables/wiki

    const datatablesSimple = document.getElementById('datatablesDosen');
    if (datatablesSimple) {
        new simpleDatatables.DataTable(datatablesSimple);
    }
});

window.addEventListener('DOMContentLoaded', event => {
    // Simple-DataTables
    // https://github.com/fiduswriter/Simple-DataTables/wiki

    const datatablesSimple = document.getElementById('datatablesPegawai');
    if (datatablesSimple) {
        new simpleDatatables.DataTable(datatablesSimple);
    }
});