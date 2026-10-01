# Elpay — Plataforma de Produtos Digitais

Laravel 13 + MySQL (`elpay`) + Breeze. Moeda: MZN. Servidor local: http://127.0.0.1:8000

## App mobile (Framework7)

PWA em http://127.0.0.1:8000/app — Framework7 v8 via CDN, consome a API REST
(token Bearer em `localStorage`): catálogo, detalhe, checkout M-Pesa/e-Mola,
conta, compras e registo/login. Novos endpoints: `GET /api/me`,
`GET /api/me/purchases`.

Para APK nativo (passo seguinte): embrulhar `/app` com Capacitor
(`npm i @capacitor/core @capacitor/cli`, `npx cap init`, `npx cap add android`,
server.url → `http://10.0.2.2:8000/app` em dev).

## Arranque

```powershell
$env:PATH = "C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64;C:\laragon\bin\composer;$env:PATH"
cd C:\laragon\www\Elpay
php artisan serve --host=127.0.0.1 --port=8000
```

Webhooks por fila — manter um worker ativo:

```powershell
php artisan queue:work --tries=3 --backoff=30 --sleep=3 --timeout=30
```

MySQL Laragon (porta 3306, user `root`, sem password). Base `elpay` já migrada e com seed.

## Contas de teste

| Email | Password | Tipo |
|---|---|---|
| admin@hotmartphp.com | admin123 | admin |
| produtor1@elpay.co.mz | 123456 | producer (KYC aprovado) |
| produtor2@elpay.co.mz | 123456 | producer (sem KYC — cai no gate) |
| afiliado@elpay.co.mz | 123456 | affiliate |
| cliente@elpay.co.mz | 123456 | client |

## Fluxos implementados

- Registo público como `producer` + verificação de email (Breeze).
- Guest checkout (`/checkout/{slug}`): cria conta `client` auto-verificada, cupom PROMO10,
  order bumps (vendas separadas ligadas por `parent_sale_id`), pagamento via gateway
  (mock aprova de imediato; `mpesa`/`emola` ficam pendentes até callback), credita produtor,
  comissão de afiliado via `?ref=CODIGO`, página de sucesso com upsell/downsell e
  definição de password.
- Pagamentos: `config/payments.php` + `app/Services/Payments/*` (interface, Mock, stubs
  M-Pesa/e-Mola com TODOs marcados). Driver ativo em `settings.payment_driver`
  (`mock` por defeito). Callback: `POST /api/payments/callback/{mpesa|emola}`.
- Cursos: produtor gere módulos/aulas em `Produtos → Conteúdo` (vídeo/texto/ficheiro);
  cliente estuda em `/learn/{produto}` com progresso; ao concluir 100% emite
  certificado (`ELPAY-XXXXXXXX`) verificável em `/c/{codigo}`.
- Ferramentas (`/tools`): order bumps, upsells, **Meta Pixels** (Facebook/TikTok/Google
  injetados no checkout), **Webhooks** (eventos `sale.paid`, `commission.created`,
  `withdrawal.*` com assinatura HMAC, entregas, teste e reenvio — via fila).
- Coprodução: produtor convida parceiros por email com % (máx. 90% total); vendas
  dividem automaticamente; ganhos em `Parcerias` + marcação admin como pago.
- Contas de saque guardadas (M-Pesa/e-Mola/banco) reutilizáveis no pedido.
- KYC obrigatório para producer/affiliate (BI, passaporte, carta) com aprovação admin.
- Carteira: saque mín. e taxa lidos de `settings` (mín. 100 MZN, taxa 2% por defeito),
  aprovação admin com estorno automático.
- Admin (`/admin`): utilizadores, produtos, vendas, pagamentos, comissões, afiliados,
  saques, KYC, relatórios, comunicação em massa, mensagens, logs, configurações.
- API (`/api`): register, login (token `api_token`), products, products/{slug}, checkout.
- Comandos: `simulate:sales --count=N`, `app:test-smtp {email}`.

## Notas

- Email por defeito em `log` (ver `storage/logs/laravel.log`). Para Gmail real, pôr
  `MAIL_MAILER=smtp`, `MAIL_USERNAME` e `MAIL_PASSWORD` (app password) no `.env`.
- Pagamentos: trocar `settings.payment_driver` para `mpesa`/`emola` e preencher
  `MPESA_*`/`EMOLA_*` no `.env`, completando os TODOs em `MpesaGateway`/`EmolaGateway`.
- Produtos `course` com módulos/aulas e certificados automáticos incluídos.
