// Call the dataTables jQuery plugin
$(document).ready(function() {
  $('#dataTable').DataTable({
    language: {
      search: "Cari...",
      lengthMenu: "_MENU_ entri setiap halaman",
      info: "Menunjukkan _START_ hingga _END_ dari _TOTAL_ entri",
      paginate: { previous: "Sebelumnya", next: "Seterusnya" },
      zeroRecords: "Tiada rekod ditemui",
      infoEmpty: "Menunjukkan 0 hingga 0 dari 0 entri",
      infoFiltered: "(ditapis dari _MAX_ jumlah rekod)"
    }
  });
});
