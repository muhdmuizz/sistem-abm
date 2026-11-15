import assert from 'node:assert/strict';
import '../public/abm-money.js';
const m=globalThis.AbmMoney;
for(const [raw,display] of [['0','0.00'],['180','180.00'],['4320','4,320.00'],['1234567.8','1,234,567.80'],['123.4567','123.46'],['39.375','39.38'],['-0.005','-0.01'],['999.999','1,000.00'],['9007199254740993.001','9,007,199,254,740,993.00'],['19455550390240544880','19,455,550,390,240,544,880.00']])assert.equal(m.format(raw),display,raw);
for(const [raw,canonical] of [['4,320.00','4320.00'],['1,234.5678','1234.5678'],['-9,007,199,254,740,993.001','-9007199254740993.001']])assert.equal(m.normalise(raw),canonical);
for(const invalid of ['1,23','1,000,00','1e3','RM 100','1.000,00'])assert.equal(m.normalise(invalid),null);
assert.equal(m.precise('123.4567'),true);assert.equal(m.precise('4320.000'),false);
const exact='9007199254740993.001';m.format(exact);assert.equal(exact,'9007199254740993.001');
console.log('Exact monetary presentation checks passed.');
