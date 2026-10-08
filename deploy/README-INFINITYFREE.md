# Deploy Elpay na InfinityFree (elpay.kesug.com) — hospedagem gratuita

Limites do plano gratuito que moldam este deploy: PHP 8.4, sem SSH/composer,
sem cron, sem email de saída, 10 s máx. por request, `symlink()` indisponível,
ficheiros até 10 MB, anti-bot `aes.js` (bloqueia curl sem cookie `__test`).

Por isso: `QUEUE_CONNECTION=sync`, `MAIL_MAILER=log`, `CACHE_STORE=database`,
uploads públicos em `htdocs/storage` via `PUBLIC_DISK_ROOT` (sem symlink).

## Layout no servidor

```
htdocs/
  index.php            # front controller (aponta para elpay-core, STAND-BY: não substituir)
  .htaccess            # Laravel public + bloqueio de zips/scripts (STAND-BY: não substituir)
  build/, *.png, ...   # resto do public/
  storage/             # ficheiros públicos (covers, materiais, avatars)
  elpay-core/          # app completa MENOS public/ (com .env e vendor/)
  core.zip / public.zip
  ifdeploy/            # scripts temporários (token) — APAGAR após uso
```

## Atualizar o código (a partir de C:\laragon\www\Elpay)

```powershell
# 1. Montar pacote (Temp, fora do repo)
$pkg = "$env:TEMP\opencode\ifpkg"
# core = tudo menos .git, node_modules, vendor, storage, tests, .env*
# public = public/* menos storage, hot, index.php, .htaccess
Compress-Archive -Path "$pkg\core\*" -DestinationPath "$pkg\core.zip" -Force
Compress-Archive -Path "$pkg\public\*" -DestinationPath "$pkg\public.zip" -Force

# 2. Subir por FTP (ftpupload.net, porta 21)
#    Fiável: espelho ficheiro-a-ficheiro (o unzip no servidor falha por ownership).
#    NÃO subir: vendor/ (inalterado), storage/ (dados do servidor), .env, index.php, .htaccess
$u = "USER:PASS"
Get-ChildItem "$pkg\core" -Recurse -File | ForEach-Object {
  $rel = $_.FullName.Substring("$pkg\core\".Length) -replace "\\","/"
  curl.exe -s -u $u -T $_.FullName "ftp://ftpupload.net/htdocs/elpay-core/$rel" --ftp-pasv --ftp-create-dirs
}
# public/* (menos storage, hot, index.php, .htaccess) -> htdocs/
# scripts ifdeploy/*.php + .iftoken (token aleatório 32 chars) para htdocs/ifdeploy/

# 3. Migrar: https://elpay.kesug.com/ifdeploy/migrate.php?token=TOKEN
#    (repetir se timeout de 10 s; migrations concluídas não repetem)

# 4. Verificar: https://elpay.kesug.com/ifdeploy/health.php?token=TOKEN
#    (USERS, ADMIN, PRODUCTS, MIGRATIONS, QUEUE=sync, PUBDISK, STORAGE_W)

# 5. Limpar via FTP: probe.php, path.php, dbcheck.php, core.zip, public.zip, ifdeploy/
```

## Seed inicial

Só com a base vazia (o `ProductionSeeder` usa `firstOrCreate`, é seguro repetir,
mas exige `ADMIN_EMAIL` + `ADMIN_PASSWORD` de 12+ chars no `.env`):

```
POST/GET https://elpay.kesug.com/ifdeploy/seed.php?token=TOKEN
```

## Notas

- `.env` do servidor vive SÓ no servidor (`htdocs/elpay-core/.env`), nunca no repo.
  Chaves: `APP_URL=https://elpay.kesug.com`, `SESSION_SECURE_COOKIE=true`,
  `PUBLIC_DISK_ROOT=/home/vol1_7/infinityfree.com/if0_43061127/htdocs/storage`.
- Email nunca sai do gratuito (sem SMTP): registo/auto-verificação funcionam,
  mas recuperação de senha e notificações por email ficam só em `storage/logs`.
- Sem cron/workers: comissões/webhooks correm em `sync` no próprio request.
- Após colar credenciais no chat, trocar a senha da conta InfinityFree.
