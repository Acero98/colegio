(() => {
    const cfg = window.asistenciaConfig, resultado = document.getElementById('resultado'), texto = document.getElementById('pendientes');
    let bloqueado = false;
    const abrir = () => new Promise((ok, fail) => { const r=indexedDB.open('colegio-asistencia',1); r.onupgradeneeded=()=>r.result.createObjectStore('pendientes',{keyPath:'uuid_cliente'}); r.onsuccess=()=>ok(r.result); r.onerror=()=>fail(r.error); });
    const lista = async () => { const db=await abrir(); return new Promise(ok=>{const r=db.transaction('pendientes').objectStore('pendientes').getAll();r.onsuccess=()=>ok(r.result||[]);}); };
    const guardar = async d => { const db=await abrir(); db.transaction('pendientes','readwrite').objectStore('pendientes').put(d); };
    const borrar = async id => { const db=await abrir(); db.transaction('pendientes','readwrite').objectStore('pendientes').delete(id); };
    const estado = async () => { const n=(await lista()).length; texto.textContent=n ? n+' marcación(es) pendiente(s) de sincronizar.' : ''; };
    const mostrar = (m,c) => { resultado.className='mt-5 rounded-xl p-4 text-center text-sm bg-'+c+'-50 text-'+c+'-700'; resultado.innerHTML=m; };
    const enviar = async d => { const r=await fetch(cfg.url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':cfg.csrf},body:JSON.stringify(d)}); const j=await r.json(); if(!r.ok||!j.ok)throw new Error(j.mensaje||'No se pudo registrar.'); return j.resultado; };
    const sync = async () => { if(!navigator.onLine)return; for(const d of await lista()){try{await enviar(Object.assign({},d,{offline:true}));await borrar(d.uuid_cliente);}catch(e){break;}} estado(); };
    const registrar = async token => {
        if(bloqueado)return; bloqueado=true;
        const d={token:token,id_dispositivo:document.getElementById('dispositivo').value,uuid_cliente:crypto.randomUUID?crypto.randomUUID():Date.now()+'-'+Math.random(),fecha_hora_dispositivo:new Date().toISOString().slice(0,19).replace('T',' ')};
        try{const r=await enviar(d);const n=r.persona.nombres+' '+r.persona.apellidos;if(r.duplicada)mostrar('<b>'+n+'</b><br>'+r.mensaje,'amber');else mostrar('<b>'+n+'</b><br>'+r.tipo+' · '+(r.estado||'registrado')+(r.tardanza?' · '+r.tardanza+' min. de tardanza':''),'emerald');}
        catch(e){if(!navigator.onLine||e instanceof TypeError){await guardar(d);estado();mostrar('Sin internet: la marcación fue guardada y se sincronizará automáticamente.','blue');}else mostrar(e.message,'red');}
        finally{setTimeout(()=>bloqueado=false,1600);}
    };
    document.addEventListener('DOMContentLoaded',async()=>{estado();sync();window.addEventListener('online',sync);if('serviceWorker'in navigator)navigator.serviceWorker.register('service-worker.js');const q=new Html5Qrcode('qr-reader');try{await q.start({facingMode:'environment'},{fps:10,qrbox:{width:240,height:240}},registrar,()=>{});}catch(e){mostrar('No se pudo abrir la cámara. Revise permisos del navegador.','red');}});
})();
