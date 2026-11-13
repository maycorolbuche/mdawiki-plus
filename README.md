# MDAWiki Plus

## GitHub Pages

1. Em **Settings → Pages → Build and deployment**, selecione **GitHub Actions** como fonte.
2. Para usar domínio próprio, adicione-o em **Settings → Pages → Custom domain** e configure o CNAME no DNS para `maycorolbuche.github.io`. Em **Settings → Secrets and variables → Actions → Variables**, crie `PUBLIC_SITE_URL` com a URL HTTPS do domínio. Sem essa variável, o build usa o domínio de produção `https://plus.mda.wiki.br/`. Para gerar uma versão servida em `github.io`, defina `PUBLIC_SITE_URL` explicitamente como `https://maycorolbuche.github.io/mdawiki-plus/`.
3. Rode o workflow **GitHub Pages** a partir da `main` em **Actions**. Os próximos pushes na `main` também publicam. O workflow grava os arquivos gerados na branch `dist` e implanta o mesmo artefato no GitHub Pages.

O build estático publica cada página e seus arquivos (CSS, imagens e outros recursos) em `/<slug>/`. Os metadados ficam em `/meta.json`, `/meta/<código>.json` e `/<slug>/meta.json`. Atualize os consumidores dessas rotas antes da troca de DNS. A raiz redireciona o navegador para o MDA Wiki por HTML.

Para gerar os arquivos do GitHub Pages localmente, rode `node scripts/pages/build.mjs --github-pages`.

## Cloudflare Pages

No painel da Cloudflare, crie um projeto **Pages** conectado ao repositório `maycorolbuche/mdawiki-plus`.

| Configuração | Valor |
| --- | --- |
| Branch de produção | `main` |
| Framework preset | `None` |
| Diretório raiz | Raiz do repositório |
| Comando de build | `node scripts/pages/build.mjs` |
| Diretório de saída | `dist` |
| Variável de build `NODE_VERSION` | `24` |
| Variável de produção `PUBLIC_SITE_URL` | URL HTTPS do domínio definitivo do Plus, usada nas tags de compartilhamento |

Habilite os deployments de prévia para testar o PR. O build gera o HTML das páginas, a página de erro e o catálogo de metadados a partir de `views/`. O Worker do Pages entrega o JSON nas URLs existentes e redireciona a raiz para o MDA Wiki. A página, o `meta.json` e os arquivos usados nela ficam em `/<slug>/`.

## Teste local

No Windows, com Node.js 24 instalado, execute na pasta do projeto:

```powershell
.\server.bat
```

Abra [http://localhost:8006/](http://localhost:8006/) para navegar pelo catálogo. O servidor local lê HTML, metadados e arquivos diretamente de `views/`. Ao salvar uma alteração, as páginas abertas são recarregadas automaticamente.

Em outros sistemas, execute:

```sh
node scripts/pages/dev.mjs
```

Para validar o build de publicação, rode `node scripts/pages/build.mjs`.

Na prévia, confira `/especialidade-de-nutricao/`, `/meta`, `/meta/CS-008`, `/especialidade-de-nutricao/meta` e uma URL inexistente. Depois de validar, adicione o domínio do Plus na aba **Custom domains** do projeto Pages e siga a orientação de DNS que o painel exibir.

O build informa a quantidade de arquivos e o tamanho do maior. No plano gratuito, o Pages aceita até 20 mil arquivos e 25 MiB por arquivo. Arquivos maiores precisam ser movidos para o R2 ou reduzidos antes do deploy.
