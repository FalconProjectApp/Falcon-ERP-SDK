---
status: active
produto: Falcon ERP SDK
---

## O que é e por que existe

Pacote compartilhado do ERP legado: middleware de tenant e utilitários comuns aos serviços daquela geração.

É pacote: chega nos projetos pelo composer, então mudança aqui afeta quem já
está em produção. Versionar com cuidado.

## Pegadinhas

- 🟠 **Quem consome trava a versão no `composer.lock`** — subir um comportamento
  novo aqui não chega sozinho nos serviços; é preciso atualizar cada um.

> Curadoria ainda rasa: escrever o *porquê* das decisões conforme o pacote for
> tocado. Formato no `CLAUDE.md` do Engineering Hub.
