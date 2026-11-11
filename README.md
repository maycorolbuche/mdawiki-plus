# MDAWiki Plus na Cloudflare Pages

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

Habilite os deployments de prévia para testar o PR. O build gera o HTML das páginas, a página de erro e o catálogo de metadados a partir de `views/`. O Worker do Pages entrega o JSON nas URLs existentes e redireciona a raiz para o MDA Wiki. Os caminhos `/views/<slug>/` continuam disponíveis para os arquivos usados nas páginas.

Para conferir o build e os limites dos arquivos localmente:

```sh
node scripts/pages/build.mjs
npx wrangler pages dev dist
```

Na prévia, confira `/especialidade-de-nutricao/`, `/meta`, `/meta/CS-008`, `/especialidade-de-nutricao/meta` e uma URL inexistente. Depois de validar, adicione o domínio do Plus na aba **Custom domains** do projeto Pages e siga a orientação de DNS que o painel exibir.

O build informa a quantidade de arquivos e o tamanho do maior. No plano gratuito, o Pages aceita até 20 mil arquivos e 25 MiB por arquivo. Arquivos maiores precisam ser movidos para o R2 ou reduzidos antes do deploy.
