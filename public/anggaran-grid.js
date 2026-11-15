(() => {
 const form=document.querySelector('[data-anggaran-grid]'); if(!form)return;
 const body=form.querySelector('[data-grid-body]'),error=form.querySelector('[data-grid-error]'),status=form.querySelector('[data-grid-status]');
 const references=JSON.parse(form.dataset.references),places=JSON.parse(form.dataset.places),editable=form.dataset.editable==='yes';
 const rows=JSON.parse(form.dataset.records); let next=0,submitting=false;
 rows.forEach(r=>r.key=++next);
 const columns=JSON.parse(form.dataset.columns),inputColumns=columns.filter(c=>c.type!=='output'),fieldNames=inputColumns.map(c=>c.key);
 const selector=form.dataset.referenceSelector,childSelector=form.dataset.referenceChildSelector,money=globalThis.AbmMoney;
 const reference=row=>{const group=references[row[selector]];return childSelector?group?.[plain(row[childSelector])]:group;};
 const fixed=(row,column)=>column.mixed&&reference(row)?.mode==='FIXED_REFERENCE';
 const exactHelp=(cell,value)=>{cell.querySelector('.money-exact')?.remove();if(!money.precise(plain(value)))return;const detail=document.createElement('details');detail.className='money-exact';const summary=document.createElement('summary');summary.textContent='Nilai tepat';const text=document.createElement('span');text.textContent=value;detail.append(summary,text);cell.append(detail);};
 const dirty=()=>{if(status)status.textContent='Perubahan belum disimpan.';form.dispatchEvent(new Event('input',{bubbles:true}));};
 const plain=value=>value===null||value===undefined?'':String(value);
 function decimalMultiply(a,b){
  if(!/^-?\d+(\.\d+)?$/.test(a)||!/^-?\d+(\.\d+)?$/.test(b))return '—';
  const scale=(a.split('.')[1]?.length||0)+(b.split('.')[1]?.length||0);
  let raw=(BigInt(a.replace('.',''))*BigInt(b.replace('.',''))).toString(),negative=raw.startsWith('-');
  if(negative)raw=raw.slice(1);
  if(scale){raw=raw.padStart(scale+1,'0');raw=raw.slice(0,-scale)+'.'+raw.slice(-scale);raw=raw.replace(/0+$/,'').replace(/\.$/,'');}
  return (negative&&raw!=='0'?'-':'')+raw;
 }
 function option(select,value,label){const o=document.createElement('option');o.value=plain(value);o.textContent=label;select.append(o);}
 function output(row,tr){
  const ref=reference(row),values={...row};
  for(const column of inputColumns)if(fixed(row,column))values[column.key]=ref.kadar;
  for(const column of columns.filter(c=>c.type==='output')){
   const columnReference=column.referenceKey ? references[column.referenceKey] : ref;
   let value=column.value??columnReference?.[column.reference];
   if(column.formula?.operation==='multiply'){
    const inputs=column.formula.inputs.map(key=>plain(values[key]));
    value=inputs.every(v=>v!=='')?inputs.reduce((a,b)=>a==='—'?'—':decimalMultiply(a,b),'1'):'—';
   }
   values[column.key]=value??'';
   const el=tr.querySelector('[data-output="'+column.key+'"]');el.dataset.exact=plain(value);el.textContent=column.money?money.format(value):value??'—';if(column.money)exactHelp(el.parentElement,value);
  }
 }
 function render(){
  body.replaceChildren();const fragment=document.createDocumentFragment();
  for(const row of rows.filter(r=>!r.deleted)){
   const tr=document.createElement('tr');tr.dataset.key=row.key;
   for(let i=0;i<columns.length+2;i++)tr.append(document.createElement('td'));
   const cells=tr.children;
   columns.forEach((column,index)=>{if(column.numeric||column.type==='decimal')cells[index+2].classList.add('grid-numeric');});
   if(row.editable&&editable){
    const check=document.createElement('input');check.type='checkbox';check.dataset.rowSelect='';check.checked=!!row.selected;check.setAttribute('aria-label','Pilih '+(row.butiran_id?'Butiran #'+row.butiran_id:'baris baharu '+row.key));
    check.addEventListener('input',event=>event.stopPropagation());check.addEventListener('change',event=>{row.selected=check.checked;event.stopPropagation();});cells[0].append(check);
   }else cells[0].textContent='—';
   cells[1].textContent=row.butiran_id?'#'+row.butiran_id:'Baharu';
   if(!row.compatible&&row.butiran_id){
    tr.className='grid-quarantine';cells[2].textContent=row.tempat_bertugas;
    cells[3].colSpan=columns.length-1;cells[3].textContent='Rekod asal dikekalkan. Penyesuaian diperlukan sebelum dipinda.';
    for(let i=columns.length+1;i>=4;i--)cells[i].remove();fragment.append(tr);continue;
   }
   inputColumns.forEach((column,index)=>{
    const name=column.key;
    const cell=cells[columns.indexOf(column)+2];
    if(!editable||!row.editable){const value=fixed(row,column)?reference(row).kadar:row[name];cell.textContent=name==='tempat_bertugas_id'?row.tempat_bertugas:column.money?money.format(value):plain(value);if(column.money)exactHelp(cell,value);return;}
    const input=document.createElement(column.type==='select'?'select':'input');input.dataset.field=name;input.id='grid-'+row.key+'-'+name;
    input.setAttribute('aria-label',column.label+' '+(row.butiran_id?'Butiran #'+row.butiran_id:'baris baharu '+row.key));
    if(column.type==='decimal'||column.type==='text'){
     input.type='text';if(column.type==='decimal')input.inputMode='decimal';
     input.maxLength=column.maxLength??(column.money?180:128);
     const value=fixed(row,column)?reference(row).kadar:row[name];input.value=column.money?money.format(value)==='—'?'':money.format(value):plain(value);
     if(column.money){exactHelp(cell,value);input.addEventListener('focus',()=>{if(!input.readOnly)input.value=plain(row[name]);});input.addEventListener('blur',()=>{if(!input.readOnly){input.value=plain(row[name])===''?'':money.format(row[name]);exactHelp(cell,row[name]);}});}
     if(fixed(row,column)){input.readOnly=true;input.dataset.fixedRate='';}
    }
    else{
     option(input,'','Pilih '+column.label);
     if(column.dependsOn){const choices=Object.keys(references[row[column.dependsOn]]||{}).filter(v=>v!=='');choices.forEach(v=>option(input,v,v));input.disabled=!choices.length;if(row[column.dependsOn]&&Object.hasOwn(references[row[column.dependsOn]]||{},'')){input.options[0].textContent='Tidak berkenaan';}}
     else if(column.options==='references')Object.keys(references).forEach(v=>option(input,v,v));else places.forEach(p=>option(input,p.id,p.nama));
     if(plain(row[name])&&!Array.from(input.options).some(o=>o.value===plain(row[name])))option(input,row[name],plain(row[name]));
     input.value=plain(row[name]);
    }
    input.addEventListener(column.type==='select'?'change':'input',()=>{
     if(!input.isConnected||input.readOnly)return;row[name]=column.money?(money.normalise(input.value)??input.value):input.value;
     if(column.type==='select'&&childSelector){
      if(name===selector)row[childSelector]=null;
      if(name===selector||name===childSelector)inputColumns.filter(c=>c.mixed).forEach(c=>row[c.key]=null);
      const key=row.key;render();body.querySelector('tr[data-key="'+key+'"] [data-field="'+name+'"]')?.focus();dirty();
     }else output(row,tr);
    });
    cell.append(input);if(column.money)exactHelp(cell,fixed(row,column)?reference(row).kadar:row[name]);
   });
   columns.forEach((column,index)=>{if(column.type!=='output')return;const el=document.createElement('output');el.dataset.output=column.key;cells[index+2].append(el);});
   output(row,tr);fragment.append(tr);
  }
  body.append(fragment);form.querySelector('[data-grid-empty]').hidden=rows.some(r=>!r.deleted);
  const removed=rows.filter(r=>r.deleted).length,undo=form.querySelector('[data-grid-undo]');if(undo){undo.hidden=!removed;undo.textContent='Batal Padam ('+removed+')';}
 }
 function add(){const r={key:++next,compatible:true,editable:true};fieldNames.forEach(key=>r[key]=key==='tempat_bertugas_id'&&places.length===1?places[0].id:null);rows.push(r);return r;}
 form.querySelector('[data-grid-add]')?.addEventListener('click',async()=>{
  const count=Number(form.querySelector('[data-grid-add-count]').value);
  if(!Number.isSafeInteger(count)||count<1){error.hidden=false;error.textContent='Masukkan bilangan baris bulat positif.';return;}
  for(let i=0;i<count;i++){add();if(i&&i%100===0)await new Promise(requestAnimationFrame);}
  render();dirty();body.lastElementChild?.querySelector('[data-field]')?.focus();
 });
 form.querySelector('[data-grid-copy]')?.addEventListener('click',()=>{
  const selected=rows.filter(r=>r.selected&&!r.deleted&&r.editable);
  for(const r of selected){const copy=add();fieldNames.forEach(f=>copy[f]=r[f]);r.selected=false;}
  if(selected.length){render();dirty();}
 });
 form.querySelector('[data-grid-remove]')?.addEventListener('click',()=>{
  for(let i=rows.length-1;i>=0;i--){const r=rows[i];if(!r.selected||!r.editable||r.deleted)continue;r.selected=false;if(r.butiran_id)r.deleted=true;else rows.splice(i,1);}
  render();dirty();
 });
 form.querySelector('[data-grid-undo]')?.addEventListener('click',()=>{rows.forEach(r=>r.deleted=false);render();dirty();});
 form.addEventListener('input',event=>{if(status&&event.target!==form.querySelector('[data-grid-add-count]'))status.textContent='Perubahan belum disimpan.';});
 // Quoted TSV supports Excel/Sheets clipboard cells without interpreting HTML or formulas.
 function tsv(text){let data=[[]],cell='',quoted=false;for(let i=0;i<text.length;i++){const c=text[i];if(c==='"'){if(quoted&&text[i+1]==='"'){cell+='"';i++;}else quoted=!quoted;}else if(!quoted&&(c==='\t'||c==='\n')){data.at(-1).push(cell.replace(/\r$/,''));cell='';if(c==='\n')data.push([]);}else cell+=c;}data.at(-1).push(cell.replace(/\r$/,''));if(data.at(-1).length===1&&data.at(-1)[0]==='')data.pop();return data;}
 body.addEventListener('paste',event=>{
  const input=event.target.closest('[data-field]'),text=event.clipboardData?.getData('text/plain');
  if(!input||!text||!/[\t\n]/.test(text)||!editable)return;
  const data=tsv(text),start=fieldNames.indexOf(input.dataset.field),visible=rows.filter(r=>!r.deleted),first=visible.findIndex(r=>r.key===Number(input.closest('tr').dataset.key));
  if(data.some(line=>line.length+start>fieldNames.length)){error.hidden=false;error.textContent='Tampal hanya lajur input: '+inputColumns.map(c=>c.label).join(', ')+'.';event.preventDefault();return;}
  event.preventDefault();
  data.forEach((line,i)=>{const row=visible[first+i]||add();if(!row.editable)return;line.forEach((value,j)=>{const name=fieldNames[start+j];if(name==='tempat_bertugas_id'){row[name]=(places.find(p=>String(p.id)===value||p.nama===value)?.id)??value;}else row[name]=columns.find(c=>c.key===name)?.money?(money.normalise(value)??value):value;});inputColumns.filter(c=>fixed(row,c)).forEach(c=>row[c.key]=null);});
  render();dirty();
 });
 function clearErrors(){error.hidden=true;error.textContent='';form.querySelectorAll('.grid-cell-error').forEach(el=>el.remove());form.querySelectorAll('[aria-invalid]').forEach(el=>{el.removeAttribute('aria-invalid');el.removeAttribute('aria-describedby');});}
 form.addEventListener('submit',async event=>{
  event.preventDefault();if(submitting)return;clearErrors();
  const retained=rows.filter(r=>!r.deleted&&r.editable&&(r.butiran_id||fieldNames.some(key=>key!=='tempat_bertugas_id'&&plain(r[key])!=='')));
  const deleted=rows.filter(r=>r.deleted),payload={semakan_bidaan:form.dataset.bidRevision,semakan_anggaran:form.dataset.estimateRevision,reason:form.querySelector('[name=reason]')?.value||null,
   rekod:retained.map(r=>({...r.butiran_id?{butiran_id:r.butiran_id,semakan_butiran:r.semakan_butiran}:{},...Object.fromEntries(fieldNames.map(key=>[key,fixed(r,columns.find(c=>c.key===key))||plain(r[key])===''?null:String(r[key])]))})),
   padam:deleted.map(r=>({butiran_id:r.butiran_id,semakan_butiran:r.semakan_butiran}))};
  const buttons=form.querySelectorAll('button[type=submit]');
  function restore(){delete form.dataset.submitting;buttons.forEach(b=>{b.disabled=false;b.removeAttribute('aria-busy');});submitting=false;}
  if(!retained.length&&!deleted.length){if(status)status.textContent='Tiada perubahan untuk disimpan.';restore();return;}
  if(deleted.length&&!window.confirm('Padam '+deleted.length+' Butiran tersimpan dan simpan perubahan lain?')){dirty();restore();return;}
  submitting=true;if(status)status.textContent='Menyimpan draf…';
  try{
   const response=await fetch(form.action,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':form.querySelector('[name=_token]').value},body:JSON.stringify(payload)});
   const data=await response.json().catch(()=>({}));
   if(response.ok&&data.url){window.location.assign(data.url);return;}
   const messages=response.status===409?['Bidaan, OS atau Butiran telah berubah. Salin perubahan anda sebelum memuat semula.']:Object.values(data.errors||{}).flat();
   error.hidden=false;error.textContent=response.redirected?'Sesi mungkin tamat. Salin perubahan anda sebelum log masuk dan memuat semula halaman.':response.status===403?'Penyediaan tidak dibenarkan untuk akses semasa. Perubahan anda dikekalkan.':messages.join(' ')||data.message||'Draf tidak disimpan. Semak akses dan cuba lagi.';
   for(const [key,values] of Object.entries(data.errors||{})){
    const match=key.match(/(?:kelompok\.)?rekod\.(\d+)\.(\w+)/);if(!match)continue;const row=retained[Number(match[1])];if(!row)continue;
    const field=body.querySelector('tr[data-key="'+row.key+'"] [data-field="'+match[2]+'"]');if(!field)continue;
    const help=document.createElement('p');help.className='grid-cell-error';help.id='grid-error-'+row.key+'-'+match[2];help.textContent=values.join(' ');field.setAttribute('aria-invalid','true');field.setAttribute('aria-describedby',help.id);field.parentElement.append(help);
   }
   error.focus();dirty();if(status)status.textContent='Gagal disimpan. Perubahan dikekalkan.';
  }catch{error.hidden=false;error.textContent='Sambungan gagal. Perubahan anda dikekalkan; cuba lagi.';error.focus();dirty();if(status)status.textContent='Gagal disimpan.';}
  restore();
 });
 render();
})();
