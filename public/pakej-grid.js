(() => {
 const form=document.querySelector('[data-pakej-grid]');if(!form)return;
 const container=form.querySelector('[data-activities]'),error=form.querySelector('[data-pakej-error]'),status=form.querySelector('[data-pakej-status]');
 const groups=JSON.parse(form.dataset.records),places=JSON.parse(form.dataset.places),references=JSON.parse(form.dataset.references),editable=form.dataset.editable==='yes',money=globalThis.AbmMoney;
 const shared=['tempat_bertugas_id','tujuan','tarikh_mula','tarikh_tamat'],fields=['kategori1','bil_unit','kadar','bil_hari'];let next=0,submitting=false;
 groups.forEach((g,i)=>{g.key=++next;g.open=i===0;g.removed=[];});
 const plain=v=>v===null||v===undefined?'':String(v),identity=g=>g.butiran_id?'#'+g.butiran_id:'Baharu '+g.key;
 const blank=()=>({id:crypto.randomUUID(),kategori1:null,bil_unit:null,kadar:null,bil_hari:null});
 function dirty(){if(status)status.textContent='Perubahan belum disimpan.';form.dispatchEvent(new Event('input',{bubbles:true}));}
 function decimalParts(value){if(!/^-?\d+(\.\d+)?$/.test(plain(value)))return null;const [whole,fraction='']=value.split('.');return [BigInt(whole+fraction),fraction.length];}
 function decimalText(integer,scale){const negative=integer<0n;let text=(negative?-integer:integer).toString();if(scale){text=text.padStart(scale+1,'0');text=text.slice(0,-scale)+'.'+text.slice(-scale);text=text.replace(/0+$/,'').replace(/\.$/,'');}return (negative?'-':'')+text;}
 function amount(c){if(!references[c.kategori1])return null;const parts=['bil_unit','kadar','bil_hari'].map(k=>decimalParts(c[k]));if(parts.some(p=>!p))return null;return decimalText(parts.reduce((a,p)=>a*p[0],1n),parts.reduce((a,p)=>a+p[1],0));}
 function subtotal(g){if(!g.komponen?.length)return null;const values=g.komponen.map(amount);if(values.some(v=>v===null))return null;const parts=values.map(decimalParts),scale=Math.max(...parts.map(p=>p[1]));return decimalText(parts.reduce((a,p)=>a+p[0]*10n**BigInt(scale-p[1]),0n),scale);}
 function exact(cell,value){cell.querySelector('.money-exact')?.remove();if(!money.precise(value))return;const detail=document.createElement('details');detail.className='money-exact';const summary=document.createElement('summary');summary.textContent='Nilai tepat';const text=document.createElement('span');text.textContent=value;detail.append(summary,text);cell.append(detail);}
 function output(el,value){el.dataset.exact=plain(value);el.textContent=money.format(value);exact(el.parentElement,value);}
 function duration(g){const parse=value=>{if(!/^\d{4}-\d{2}-\d{2}$/.test(plain(value)))return null;const [y,m,d]=value.split('-').map(Number),date=new Date(0);date.setUTCFullYear(y,m-1,d);date.setUTCHours(0,0,0,0);return date.getUTCFullYear()===y&&date.getUTCMonth()===m-1&&date.getUTCDate()===d?date.getTime():null;};const a=parse(g.tarikh_mula),b=parse(g.tarikh_tamat);return a!==null&&b!==null&&b>=a?String((b-a)/86400000+1):'—';}
 function summary(g,section){section.querySelector('[data-title]').textContent=g.tujuan||'Aktiviti Baharu';section.querySelector('[data-meta]').textContent=identity(g)+' · '+(g.komponen?.length||0)+' komponen';section.querySelector('[data-summary-total]').textContent='RM '+money.format(subtotal(g));const total=section.querySelector('[data-subtotal]');if(total)output(total,subtotal(g));const days=section.querySelector('[data-calendar-days]');if(days)days.textContent=duration(g);}
 function button(text,action,danger=false){const b=document.createElement('button');b.type='button';b.className='button'+(danger?' danger':'');b.textContent=text;b.addEventListener('click',action);return b;}
 function render(){
  container.replaceChildren();
  for(const g of groups.filter(g=>!g.deleted)){
   const section=document.createElement('details');section.className='pakej-activity';section.dataset.activity=g.key;section.open=!!g.open;
   section.innerHTML='<summary><span class="pakej-title"><span data-title></span><span class="small pakej-meta" data-meta></span></span><span class="pakej-total" data-summary-total></span></summary><div data-content></div>';
   section.addEventListener('toggle',()=>g.open=section.open);const content=section.querySelector('[data-content]');
   const canEdit=editable&&g.editable;
   if(!g.compatible){const p=document.createElement('p');p.className='notice';p.textContent='Rekod memerlukan penyesuaian sebelum dipinda.';content.append(p);summary(g,section);container.append(section);continue;}
   const sharedBox=document.createElement('div');sharedBox.className='pakej-shared';
   for(const [key,label,type] of [['tujuan','Nama Program/ Aktiviti','text'],['tempat_bertugas_id','Tempat Bertugas','select'],['tarikh_mula','Tarikh Mula','date'],['tarikh_tamat','Tarikh Tamat','date']]){
    const wrap=document.createElement('label');wrap.textContent=label;let input;
    if(canEdit){input=document.createElement(type==='select'?'select':'input');input.dataset.shared=key;input.setAttribute('aria-label',label+' '+identity(g));
     if(type==='select'){const choices=[{id:'',nama:'Pilih Tempat Bertugas'},...places];choices.forEach(p=>{const o=document.createElement('option');o.value=p.id;o.textContent=p.nama;input.append(o);});}
     else{input.type=type;if(type==='date'){input.min=form.dataset.year+'-01-01';input.max=form.dataset.year+'-12-31';}else input.maxLength=500;}
     input.value=plain(g[key]);input.addEventListener(type==='select'?'change':'input',()=>{g[key]=input.value;summary(g,section);dirty();});
    }else{input=document.createElement('span');input.style.display='block';input.style.marginTop='8px';input.textContent=key==='tempat_bertugas_id'?g.tempat_bertugas:type==='date'&&g[key]?g[key].split('-').reverse().join('/'):plain(g[key])||'—';}
    wrap.append(input);sharedBox.append(wrap);
   }
   const calendar=document.createElement('p');calendar.className='small pakej-date-duration';calendar.innerHTML='Tempoh Kalendar: <output data-calendar-days></output> hari';sharedBox.append(calendar);content.append(sharedBox);
   const componentBox=document.createElement('div');componentBox.className='pakej-components';
   componentBox.innerHTML='<div class="anggaran-grid-scroll" tabindex="0" role="region" aria-label="Komponen Pakej boleh ditatal mendatar"><table class="anggaran-grid"><caption class="sr-only">Komponen aktiviti Pakej</caption><thead><tr><th>Pilih</th><th>Komponen</th><th class="grid-numeric">Bil. Unit</th><th>Unit</th><th class="grid-numeric">Kadar (RM)</th><th class="grid-numeric">Bilangan Hari/ Malam</th><th class="grid-numeric">Anggaran (RM) — Pratonton</th></tr></thead><tbody></tbody></table></div>';
   const tbody=componentBox.querySelector('tbody');
   g.komponen.forEach((c,index)=>{
    const tr=document.createElement('tr');tr.dataset.component=c.id;
    for(let i=0;i<7;i++)tr.append(document.createElement('td'));
    const cells=tr.children;let check;
    if(canEdit){check=document.createElement('input');check.type='checkbox';check.checked=!!c.selected;check.setAttribute('aria-label','Pilih komponen '+(index+1)+' '+identity(g));check.dataset.componentSelect='';check.addEventListener('input',event=>event.stopPropagation());check.addEventListener('change',()=>c.selected=check.checked);cells[0].append(check);}else cells[0].textContent='—';
    for(const [key,col] of [['kategori1',1],['bil_unit',2],['kadar',4],['bil_hari',5]]){
     const cell=cells[col];if(key!=='kategori1')cell.className='grid-numeric';
     if(canEdit){const input=document.createElement(key==='kategori1'?'select':'input');input.dataset.field=key;input.setAttribute('aria-label',key==='kategori1'?'Komponen '+(index+1):key==='kadar'?'Kadar (RM)':key==='bil_unit'?'Bil. Unit':'Bilangan Hari/ Malam');
      if(key==='kategori1'){for(const name of ['',...Object.keys(references)]){const o=document.createElement('option');o.value=name;o.textContent=name||'Pilih Komponen';input.append(o);}}
      else{input.type='text';input.inputMode='decimal';input.maxLength=key==='kadar'?180:128;}
      input.value=key==='kadar'?(plain(c[key])===''?'':money.format(c[key])):plain(c[key]);
      if(key==='kadar'){input.addEventListener('focus',()=>input.value=plain(c[key]));input.addEventListener('blur',()=>{input.value=plain(c[key])===''?'':money.format(c[key]);exact(cell,c[key]);});exact(cell,c[key]);}
      input.addEventListener(key==='kategori1'?'change':'input',()=>{c[key]=key==='kadar'?(money.normalise(input.value)??input.value):input.value;update();dirty();});cell.prepend(input);
     }else{const value=document.createElement('span');value.textContent=key==='kadar'?money.format(c[key]):plain(c[key])||'—';cell.append(value);if(key==='kadar')exact(cell,c[key]);}
    }
    const basis=document.createElement('p'),charge=document.createElement('p');basis.className=charge.className='small';basis.dataset.basis='';charge.dataset.chargeLabel='';basis.id='pakej-'+g.key+'-'+c.id+'-basis';charge.id='pakej-'+g.key+'-'+c.id+'-charge';cells[4].append(basis);cells[5].append(charge);
    if(canEdit){cells[4].querySelector('input').setAttribute('aria-describedby',basis.id);cells[5].querySelector('input').setAttribute('aria-describedby',charge.id);}
    const preview=document.createElement('output');preview.dataset.componentAmount='';cells[6].className='grid-numeric';cells[6].append(preview);
    function update(){const ref=references[c.kategori1];cells[3].textContent=ref?.unit||'—';basis.textContent=ref?.basis||'';charge.textContent=ref?.duration_label||'';const field=cells[5].querySelector('input');if(field)field.setAttribute('aria-label',(ref?.duration_label||'Bilangan Hari/ Malam')+' komponen '+(index+1));output(preview,amount(c));summary(g,section);}
    tbody.append(tr);update();
   });
   if(!g.komponen.length){const tr=document.createElement('tr'),td=document.createElement('td');td.colSpan=7;td.textContent='Tiada komponen.';td.className='empty-state';tr.append(td);tbody.append(tr);}
   if(canEdit){
    const actions=document.createElement('div');actions.className='pakej-actions';
    actions.append(button('Tambah Komponen',()=>{g.komponen.push(blank());g.open=true;render();container.querySelector('[data-activity="'+g.key+'"] tbody tr:last-child select')?.focus();}),button('Salin Komponen Dipilih',()=>{const copies=g.komponen.filter(c=>c.selected).map(c=>({...c,id:crypto.randomUUID(),selected:false}));g.komponen.forEach(c=>c.selected=false);g.komponen.push(...copies);render();if(copies.length)dirty();}),button('Padam Komponen Dipilih',()=>{g.removed.push(...g.komponen.map((c,i)=>({c,i})).filter(({c})=>c.selected));g.komponen=g.komponen.filter(c=>!c.selected);render();dirty();},true));
    // Adding a component is a draft change even before any of its cells is filled.
    actions.firstChild.addEventListener('click',dirty);
    if(g.removed.length)actions.append(button('Batal Padam Komponen',()=>{for(const {c,i} of [...g.removed].reverse()){c.selected=false;g.komponen.splice(i,0,c);}g.removed=[];render();dirty();}));componentBox.append(actions);
    const help=document.createElement('p');help.className='small pakej-component-help';help.textContent='Tampal lajur input: Komponen, Bil. Unit, Kadar, Bilangan Hari/ Malam. Tarikh tidak menetapkan malam atau hari bercaj.';componentBox.append(help);
    tbody.addEventListener('paste',event=>{
     const input=event.target.closest('[data-field]'),text=event.clipboardData?.getData('text/plain');if(!input||!text||!/[\t\n]/.test(text))return;
     const data=text.trimEnd().split(/\r?\n/).map(line=>line.split('\t')),start=fields.indexOf(input.dataset.field),first=g.komponen.findIndex(c=>c.id===input.closest('tr').dataset.component);
     event.preventDefault();if(data.some(line=>line.length+start>fields.length)){error.hidden=false;error.textContent='Tampal lajur input komponen sahaja.';return;}
     data.forEach((line,i)=>{const c=g.komponen[first+i]||blank();if(!g.komponen[first+i])g.komponen.push(c);line.forEach((value,j)=>{const name=fields[start+j];c[name]=name==='kadar'?(money.normalise(value)??value):value;});});render();dirty();
    });
   }
   const footer=document.createElement('div');footer.className='pakej-actions';
   if(canEdit){footer.append(button('Salin Aktiviti',()=>{const copy={...g,key:++next,komponen:g.komponen.map(c=>({...c,id:crypto.randomUUID(),selected:false})),removed:[],open:true};delete copy.butiran_id;delete copy.semakan_butiran;groups.push(copy);render();dirty();}),button('Padam Aktiviti',()=>{g.deleted=true;render();dirty();},true));}
   const total=document.createElement('div');total.className='pakej-total';total.innerHTML='Jumlah Pakej — Pratonton <output data-subtotal aria-live="polite"></output>';footer.append(total);componentBox.append(footer);content.append(componentBox);summary(g,section);container.append(section);
  }
  form.querySelector('[data-pakej-empty]').hidden=groups.some(g=>!g.deleted);const undo=form.querySelector('[data-undo-activity]');if(undo)undo.hidden=!groups.some(g=>g.deleted);
 }
 form.querySelector('[data-add-activity]')?.addEventListener('click',()=>{const g={key:++next,editable:true,compatible:true,open:true,komponen:[],removed:[],tujuan:null,tarikh_mula:null,tarikh_tamat:null,tempat_bertugas_id:places.length===1?places[0].id:null};groups.push(g);render();dirty();container.lastElementChild.querySelector('[data-shared=tujuan]')?.focus();});
 form.querySelector('[data-undo-activity]')?.addEventListener('click',()=>{groups.forEach(g=>g.deleted=false);render();dirty();});
 function resetSubmit(){delete form.dataset.submitting;form.querySelectorAll('button[type=submit]').forEach(b=>{b.disabled=false;b.removeAttribute('aria-busy');});}
 function hasInput(g){return shared.some(k=>k!=='tempat_bertugas_id'&&plain(g[k])!=='')||g.komponen.some(c=>fields.some(k=>plain(c[k])!==''));}
 form.addEventListener('submit',async event=>{
  event.preventDefault();if(submitting)return;error.hidden=true;error.textContent='';form.querySelectorAll('.grid-cell-error').forEach(el=>el.remove());form.querySelectorAll('[aria-invalid]').forEach(el=>{el.removeAttribute('aria-invalid');const ids=(el.getAttribute('aria-describedby')||'').split(' ').filter(id=>id&&!id.startsWith('pakej-error-'));if(ids.length)el.setAttribute('aria-describedby',ids.join(' '));else el.removeAttribute('aria-describedby');});
  const retained=groups.filter(g=>g.editable&&!g.deleted&&(g.butiran_id||hasInput(g))),deleted=groups.filter(g=>g.deleted&&g.butiran_id);
  if(!retained.length&&!deleted.length){if(status)status.textContent='Tiada perubahan untuk disimpan.';resetSubmit();return;}
  if(deleted.length&&!window.confirm('Padam '+deleted.length+' aktiviti tersimpan bersama komponennya dan simpan perubahan lain?')){resetSubmit();dirty();return;}
  const payload={semakan_bidaan:form.dataset.bidRevision,semakan_anggaran:form.dataset.estimateRevision,reason:form.querySelector('[name=reason]')?.value||null,
   rekod:retained.map(g=>({...g.butiran_id?{butiran_id:g.butiran_id,semakan_butiran:g.semakan_butiran}:{},...Object.fromEntries(shared.map(k=>[k,plain(g[k])===''?null:String(g[k])])),komponen:g.komponen.map(c=>({id:c.id,...Object.fromEntries(fields.map(k=>[k,plain(c[k])===''?null:String(c[k])]))}))})),padam:deleted.map(g=>({butiran_id:g.butiran_id,semakan_butiran:g.semakan_butiran}))};
  submitting=true;form.dataset.submitting='yes';const controls=Array.from(form.querySelectorAll('button,input,select,textarea'),el=>[el,el.matches('button[type=submit]')?false:el.disabled]);controls.forEach(([el])=>el.disabled=true);if(status)status.textContent='Menyimpan draf…';
  try{
   const response=await fetch(form.action,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':form.querySelector('[name=_token]').value},body:JSON.stringify(payload)});const data=await response.json().catch(()=>({}));
   if(response.ok&&data.url){window.location.assign(data.url);return;}
   error.hidden=false;error.textContent=response.redirected?'Sesi mungkin tamat. Salin perubahan anda sebelum log masuk semula.':response.status===409?'Bidaan, OS atau aktiviti telah berubah. Salin perubahan anda sebelum memuat semula.':response.status===403?'Penyediaan tidak dibenarkan untuk akses semasa. Perubahan dikekalkan.':Object.values(data.errors||{}).flat().join(' ')||data.message||'Draf tidak disimpan. Cuba lagi.';
   for(const [key,messages] of Object.entries(data.errors||{})){
    const match=key.match(/(?:kelompok\.)?rekod\.(\d+)\.(?:(?:komponen)\.(\d+)\.)?(\w+)$/);if(!match)continue;const g=retained[Number(match[1])];if(!g)continue;
    const section=container.querySelector('[data-activity="'+g.key+'"]');if(!section)continue;section.open=true;g.open=true;
    const target=match[2]!==undefined?section.querySelector('[data-component="'+g.komponen[Number(match[2])]?.id+'"] [data-field="'+match[3]+'"]'):section.querySelector('[data-shared="'+match[3]+'"]');if(!target)continue;
    const p=document.createElement('p');p.id='pakej-error-'+g.key+'-'+key.replaceAll('.','-');p.className='grid-cell-error';p.textContent=messages.join(' ');target.setAttribute('aria-invalid','true');target.setAttribute('aria-describedby',[(target.getAttribute('aria-describedby')||''),p.id].filter(Boolean).join(' '));target.parentElement.append(p);
   }
   error.focus();
  }catch{error.hidden=false;error.textContent='Sambungan gagal. Perubahan anda dikekalkan; cuba lagi.';error.focus();}
  submitting=false;delete form.dataset.submitting;controls.forEach(([el,disabled])=>el.disabled=disabled);resetSubmit();dirty();if(status)status.textContent='Gagal disimpan. Perubahan dikekalkan.';
 });
 render();
})();
