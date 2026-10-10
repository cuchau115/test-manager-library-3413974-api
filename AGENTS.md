# AGENTS.md — repo `api` (backend Laravel 11)

> **Lee primero el plan maestro completo:** `../AGENTS.md` (raíz del proyecto). Copia canónica
> versionada: `../test-manager-library-3413974-docs/AGENTS.md`. Este archivo es solo un puntero.

## Reglas duras (resumen; el detalle está en el maestro §1)
- Todo con **Docker**; nunca XAMPP ni PHP en el host.
- Se construye **leyendo el spec** (`../test-manager-library-3413974-docs/spec-python/`), no de memoria.
- **Commits pequeños** y **push** a `origin` (fork `cuchau115/test-manager-library-3413974-api`),
  nunca a `code-sena/…`.
- Arquitectura hexagonal: `app/Domain` PHP puro; puertos en `app/Application/Ports`; único punto de
  composición en `CompositionServiceProvider`.
- Toda tarea exige **evidencia** reproducible (`php artisan test --filter=…`).

## Workstreams y tareas de este repo (maestro §9.1–§9.6)
- `api-found`: `T-01.1`…`T-01.5`
- `api-domain`: `T-03.1`…`T-06.3`
- `api-app`: `T-07.1`…`T-11.2`
- `api-adapters`: `T-02.1`…`T-15.2`
- `api-http`: `T-16.1`…`T-17.3`
- `api-tests`: `T-16.10`, `T-16.11`, `T-18.1`, `T-18.2`, `T-19.1`

Un solo agente por árbol de trabajo; para paralelizar dentro de `api`, usar worktrees (maestro §7).
