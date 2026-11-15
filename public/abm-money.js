/* Presentation only. Exact decimal strings stay authoritative for editing and calculation. */
(() => {
 const normalise=value=>typeof value==='string'&&/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/.test(value)?value.replaceAll(',',''):null;
 const format=value=>{
  const raw=normalise(value);if(raw===null)return value==null||value===''?'—':String(value);
  const negative=raw.startsWith('-'),unsigned=negative?raw.slice(1):raw,[whole,fraction='']=unsigned.split('.');
  // Round the display magnitude only; never feed the displayed text back into source arithmetic.
  let cents=BigInt(whole)*100n+BigInt((fraction+'00').slice(0,2));
  if(fraction.length>2&&fraction[2]>='5')cents+=1n;
  const digits=cents.toString().padStart(3,'0'),integer=digits.slice(0,-2).replace(/\B(?=(\d{3})+(?!\d))/g,',');
  return (negative&&cents!==0n?'-':'')+integer+'.'+digits.slice(-2);
 };
 const precise=value=>{const raw=normalise(value);return raw!==null&&/\.[0-9]{2}[0-9]*[1-9][0-9]*$/.test(raw);};
 globalThis.AbmMoney=Object.freeze({normalise,format,precise});
})();
