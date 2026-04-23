document.querySelectorAll('.toggle-pwd').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const span = btn.previousElementSibling;
    const real = span.dataset.pwd;
    if (span.textContent === '••••••') { span.textContent = real; }
    else { span.textContent = '••••••'; }
  });
});

document.querySelectorAll('.ping-btn').forEach(btn=>{
  btn.addEventListener('click', async ()=>{
    btn.disabled = true; btn.textContent = '…';
    const r = await fetch('/api/ping.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({url:btn.dataset.url,id:btn.dataset.id})});
    const data = await r.json();
    const cell = btn.parentElement;
    const status = cell.querySelector('.status');
    status.className = 'status ' + (data.ok?'ok':'fail');
    status.textContent = data.ok ? ('HTTP '+data.status) : 'Inacessível';
    btn.disabled = false; btn.textContent = '↻';
  });
});

// Auto-ping ao carregar dashboard (sistemas sem ping recente)
window.addEventListener('load', ()=>{
  document.querySelectorAll('.ping-btn').forEach((btn,i)=>{
    const cell = btn.parentElement;
    if (cell.querySelector('.status.pending')) {
      setTimeout(()=>btn.click(), 200*i);
    }
  });
});
