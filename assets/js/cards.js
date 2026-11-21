document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.qrbox').forEach(function (el) {
    new QRCode(el, { text: 'QRSA:' + el.dataset.token, width: 160, height: 160, correctLevel: QRCode.CorrectLevel.M });
  });
  var btn = document.getElementById('dlAll');
  if (btn) btn.addEventListener('click', async function () {
    var cards = document.querySelectorAll('.idcard');
    if (cards.length > 30 && !confirm('Mengunduh ' + cards.length + ' gambar sekaligus bisa berat. Lanjutkan?')) return;
    for (var i = 0; i < cards.length; i++) {
      var canvas = await html2canvas(cards[i], { scale: 3, useCORS: true, backgroundColor: '#ffffff' });
      var a = document.createElement('a'); a.download = 'kartu-' + cards[i].dataset.nis + '.png'; a.href = canvas.toDataURL('image/png'); a.click();
      await new Promise(function (r) { setTimeout(r, 250); });
    }
  });
});
