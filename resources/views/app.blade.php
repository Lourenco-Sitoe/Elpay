<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Elpay App</title>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#168a45">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Elpay">
<link rel="apple-touch-icon" href="/pwa-icons/icon-192.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/framework7@8/framework7-bundle.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
  :root { --f7-theme-color: #168a45; --f7-font-family: "Plus Jakarta Sans", system-ui, sans-serif; }
  .price { color: var(--f7-theme-color); font-weight: 800; }
  .product-img { height: 150px; object-fit: cover; width: 100%; border-radius: 12px 12px 0 0; }
  .product-fallback { height: 150px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg,#dbeafe,#eff6ff); border-radius: 12px 12px 0 0; font-size: 48px; color: #93c5fd; }
  .pay-card input[type=radio] { accent-color: var(--f7-theme-color); }
</style>
</head>
<body>
<div id="app">
  <div class="view view-main view-init safe-areas" data-url="/">
    <div class="page" data-name="home">
      <div class="navbar"><div class="navbar-bg"></div><div class="navbar-inner"><div class="title">⚡ Elpay</div><div class="right"><a href="/account/" class="link icon-only"><i class="icon bi bi-person-circle" style="font-size:22px"></i></a></div></div></div>
      <div class="page-content">
        <div class="block">
          <div class="card" style="background:linear-gradient(135deg,#168a45,#12703a);color:#fff;border:none">
            <div class="card-content card-content-padding">
              <b style="font-size:18px">Produtos digitais em MZN</b>
              <p style="opacity:.85">Paga com M-Pesa ou e-Mola. Acesso imediato.</p>
            </div>
          </div>
        </div>
        <div class="block-title">Em destaque</div>
        <div class="list media-list inset" style="background:transparent"><ul id="product-list"></ul></div>
        <div class="block block-strong inset" id="home-empty" style="display:none;text-align:center;color:#888">Sem produtos de momento.</div>
      </div>
    </div>
  </div>
</div>

<template id="tpl-product">
  <div class="page" data-name="product">
    <div class="navbar"><div class="navbar-bg"></div><div class="navbar-inner sliding"><div class="left"><a href="#" class="link back"><i class="icon icon-back"></i><span>Voltar</span></a></div><div class="title">Produto</div></div></div>
    <div class="page-content"><div class="block" id="product-detail"></div></div>
  </div>
</template>

<template id="tpl-checkout">
  <div class="page" data-name="checkout">
    <div class="navbar"><div class="navbar-bg"></div><div class="navbar-inner sliding"><div class="left"><a href="#" class="link back"><i class="icon icon-back"></i><span>Voltar</span></a></div><div class="title">Checkout</div></div></div>
    <div class="page-content"><div class="block" id="checkout-box"></div></div>
  </div>
</template>

<template id="tpl-auth">
  <div class="page" data-name="auth">
    <div class="navbar"><div class="navbar-bg"></div><div class="navbar-inner sliding"><div class="left"><a href="#" class="link back"><i class="icon icon-back"></i><span>Voltar</span></a></div><div class="title" id="auth-title">Entrar</div></div></div>
    <div class="page-content"><div class="block" id="auth-box"></div></div>
  </div>
</template>

<template id="tpl-account">
  <div class="page" data-name="account">
    <div class="navbar"><div class="navbar-bg"></div><div class="navbar-inner sliding"><div class="left"><a href="#" class="link back"><i class="icon icon-back"></i><span>Voltar</span></a></div><div class="title">Minha conta</div></div></div>
    <div class="page-content"><div class="block" id="account-box"></div></div>
  </div>
</template>

<script src="https://cdn.jsdelivr.net/npm/framework7@8/framework7-bundle.min.js"></script>
<script>
var app = new Framework7({ el: '#app', name: 'Elpay', theme: 'auto', colors: { primary: '#168a45' },
  routes: [
    { path: '/product/:id/', template: document.getElementById('tpl-product').innerHTML, on: { pageInit: function(e, page){ loadProduct(page.route.params.id); } } },
    { path: '/checkout/:id/', template: document.getElementById('tpl-checkout').innerHTML, on: { pageInit: function(e, page){ loadCheckout(page.route.params.id); } } },
    { path: '/auth/', template: document.getElementById('tpl-auth').innerHTML, on: { pageInit: showAuth } },
    { path: '/account/', template: document.getElementById('tpl-account').innerHTML, on: { pageInit: loadAccount } },
  ],
});
var mainView = app.views.create('.view-main');
var API = '/api';
var token = () => localStorage.getItem('elpay_token') || '';
function headers(json){ const h = {}; if(json) h['Content-Type']='application/json'; if(token()) h['Authorization']='Bearer '+token(); return h; }
function money(v){ return Number(v).toFixed(2) + ' MT'; }
function toast(t){ app.toast.create({ text: t, closeTimeout: 2500 }).open(); }

async function loadProducts(){
  try {
    const r = await fetch(API + '/products', { headers: headers() });
    const j = await r.json();
    const items = j.data || j;
    const ul = document.getElementById('product-list');
    document.getElementById('home-empty').style.display = items.length ? 'none' : 'block';
    ul.innerHTML = items.map(p => `
      <li><a href="/product/${p.id}/" class="item-link item-content" style="background:#fff;border-radius:12px;margin-bottom:10px;box-shadow:0 1px 4px rgba(0,0,0,.08)">
        ${p.cover ? `<img class="product-img" src="/storage/${p.cover}">` : `<div class="product-fallback">📦</div>`}
        <div class="item-inner" style="padding:10px 14px"><div class="item-title-row"><div class="item-title" style="font-weight:700">${p.title}</div></div>
        <div class="item-text" style="color:#888">${(p.description||'').substring(0,80)}</div>
        <div style="margin-top:6px"><span class="price">${money(p.price)}</span></div></div>
      </a></li>`).join('');
  } catch(e){ toast('Erro a carregar produtos'); }
}

async function loadProduct(id){
  const box = document.getElementById('product-detail');
  box.innerHTML = '<div class="preloader"></div>';
  let list = [];
  try {
    const r = await fetch(API + '/products', { headers: headers() });
    const j = await r.json(); list = j.data || j;
  } catch(e){}
  const p = list.find(x => String(x.id) === String(id));
  if(!p){ box.innerHTML = '<p>Produto não encontrado.</p>'; return; }
  box.innerHTML = `
    <div class="card" style="margin:0">
      ${p.cover ? `<img class="product-img" style="height:200px" src="/storage/${p.cover}">` : `<div class="product-fallback" style="height:200px">📦</div>`}
      <div class="card-content card-content-padding">
        <h2 style="margin:0">${p.title}</h2>
        <p style="color:#666">${p.description||''}</p>
        <p class="price" style="font-size:24px">${money(p.price)}</p>
        <a href="/checkout/${p.id}/" class="button button-fill button-large" style="margin-top:8px">Comprar agora</a>
      </div>
    </div>`;
  box.dataset.product = JSON.stringify(p);
}

function loadCheckout(id){
  const box = document.getElementById('checkout-box');
  if(!token()){ location.hash = ''; mainView.router.navigate('/auth/'); toast('Entra primeiro para comprar'); return; }
  box.innerHTML = `
    <form id="co-form" class="list inset" style="background:#fff;border-radius:12px">
      <ul>
        <li class="item-content item-input"><div class="item-inner"><div class="item-title item-label">Método</div>
          <div class="item-input-wrap" style="display:flex;gap:16px;padding:8px 0">
            <label class="pay-card"><input type="radio" name="method" value="mpesa" checked> <b style="color:#e11d48">M-Pesa</b></label>
            <label class="pay-card"><input type="radio" name="method" value="emola"> <b style="color:#f97316">e-Mola</b></label>
          </div></div></li>
      </ul>
      <div class="block"><button type="submit" class="button button-fill button-large">Pagar</button></div>
    </form><div id="co-result"></div>`;
  document.getElementById('co-form').addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const method = document.querySelector('input[name=method]:checked').value;
    app.preloader.show();
    try {
      const r = await fetch(API + '/checkout', { method: 'POST', headers: headers(true),
        body: JSON.stringify({ product_id: Number(id), payment_method: method }) });
      const j = await r.json();
      app.preloader.hide();
      if(r.status === 201 || r.status === 202){
        const s = j.sale || {};
        document.getElementById('co-result').innerHTML = `
          <div class="card" style="border:2px solid #168a45"><div class="card-content card-content-padding" style="text-align:center">
          <div style="font-size:48px">✅</div><h3>${r.status===201 ? 'Pagamento recebido!' : 'Aguarda confirmação no telemóvel…'}</h3>
          <p>Compra #${s.id||''} · ${s.final_price ? money(s.final_price) : ''}</p>
          <a href="/account/" class="button button-fill">Ver minhas compras</a></div></div>`;
      } else { toast(j.message || 'Falha no pagamento'); }
    } catch(e){ app.preloader.hide(); toast('Erro de rede'); }
  });
}

function showAuth(){
  const box = document.getElementById('auth-box');
  const login = !token() || true;
  box.innerHTML = `
    <div class="list inset" style="background:#fff;border-radius:12px"><ul>
      <li class="item-content item-input" id="row-name" style="display:none"><div class="item-inner"><div class="item-title item-label">Nome</div><div class="item-input-wrap"><input name="name" type="text" placeholder="O teu nome"></div></div></li>
      <li class="item-content item-input"><div class="item-inner"><div class="item-title item-label">Email</div><div class="item-input-wrap"><input name="email" type="email" placeholder="tu@email.co.mz"></div></div></li>
      <li class="item-content item-input"><div class="item-inner"><div class="item-title item-label">Password</div><div class="item-input-wrap"><input name="password" type="password" placeholder="••••••••"></div></div></li>
    </ul></div>
    <div class="block">
      <button id="btn-auth" class="button button-fill button-large">Entrar</button>
      <button id="btn-swap" class="button button-clear" style="margin-top:8px">Criar conta nova</button>
    </div>`;
  let mode = 'login';
  document.getElementById('btn-swap').onclick = () => {
    mode = mode === 'login' ? 'register' : 'login';
    document.getElementById('row-name').style.display = mode === 'register' ? '' : 'none';
    document.getElementById('auth-title').textContent = mode === 'register' ? 'Criar conta' : 'Entrar';
    document.getElementById('btn-auth').textContent = mode === 'register' ? 'Registar' : 'Entrar';
    document.getElementById('btn-swap').textContent = mode === 'register' ? 'Já tenho conta' : 'Criar conta nova';
  };
  document.getElementById('btn-auth').onclick = async () => {
    const email = box.querySelector('input[name=email]').value.trim();
    const password = box.querySelector('input[name=password]').value;
    const name = (box.querySelector('input[name=name]')||{}).value || 'Utilizador App';
    if(!email || password.length < 4){ toast('Preenche email e password'); return; }
    app.preloader.show();
    try {
      const url = API + (mode === 'register' ? '/register' : '/login');
      const body = mode === 'register' ? { name, email, password } : { email, password };
      const r = await fetch(url, { method: 'POST', headers: headers(true), body: JSON.stringify(body) });
      const j = await r.json();
      app.preloader.hide();
      if(j.token){ localStorage.setItem('elpay_token', j.token); toast('Bem-vindo, ' + (j.user.name||'')); mainView.router.back(); loadProducts(); }
      else toast(j.message || 'Falhou. Verifica os dados.');
    } catch(e){ app.preloader.hide(); toast('Erro de rede'); }
  };
}

async function loadAccount(){
  const box = document.getElementById('account-box');
  if(!token()){ mainView.router.navigate('/auth/'); return; }
  box.innerHTML = '<div class="preloader"></div>';
  try {
    const me = await (await fetch(API + '/me', { headers: headers() })).json();
    const pu = await (await fetch(API + '/me/purchases', { headers: headers() })).json();
    const items = pu.data || pu;
    box.innerHTML = `
      <div class="card"><div class="card-content card-content-padding">
        <h3 style="margin:0">${me.name||''}</h3><p style="color:#666;margin:4px 0">${me.email||''} · ${me.type||''}</p>
        <p>Saldo: <b class="price">${money(me.balance||0)}</b></p>
        <button id="btn-out" class="button button-outline" style="margin-top:8px">Sair</button>
      </div></div>
      <div class="block-title">Minhas compras (${items.length||0})</div>
      <div class="list media-list inset">${(items||[]).map(a => `
        <li><div class="item-content" style="background:#fff;border-radius:12px;margin-bottom:8px"><div class="item-inner" style="padding:10px 14px">
        <div class="item-title" style="font-weight:700">${(a.product&&a.product.title)||'Produto'}</div>
        <div class="item-text">#${a.sale_id} · ${a.created_at||''}</div></div></div></li>`).join('') || '<p style="text-align:center;color:#888">Ainda sem compras.</p>'}</div>`;
    document.getElementById('btn-out').onclick = () => { localStorage.removeItem('elpay_token'); toast('Sessão terminada'); mainView.router.back(); };
  } catch(e){ box.innerHTML = '<p>Sessão inválida. <a href="/auth/">Entra de novo</a>.</p>'; }
}

loadProducts();
</script>
</body>
</html>
