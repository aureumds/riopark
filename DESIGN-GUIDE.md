# 🎨 Rio Park - Guia de Design

> Guia de referência para manter consistência visual em todo o sistema

## 📋 Índice
- [Paleta de Cores](#paleta-de-cores)
- [Tipografia](#tipografia)
- [Componentes](#componentes)
- [Layouts](#layouts)
- [Responsividade](#responsividade)

---

## 🎨 Paleta de Cores

### Cores Principais
```css
/* Azul Principal (Brand) */
bg-blue-800   /* Escuro #1e40af */
bg-blue-600   /* Médio #2563eb */
bg-blue-50    /* Claro #eff6ff */

/* Cinzas (Texto e Fundos) */
bg-slate-900  /* Texto principal */
bg-slate-700  /* Texto secundário */
bg-slate-500  /* Texto terciário */
bg-slate-100  /* Fundo secundário */
bg-slate-50   /* Fundo hover */
```

### Cores Semânticas
```css
/* Sucesso */
bg-emerald-700 text-emerald-700 bg-emerald-50

/* Atenção */
bg-amber-700 text-amber-700 bg-amber-50

/* Erro */
bg-red-700 text-red-700 bg-red-50

/* Informação */
bg-violet-700 text-violet-700 bg-violet-50
```

---

## 📝 Tipografia

### Hierarquia de Títulos
```blade
{{-- Título da Página --}}
<h1 class="text-xl sm:text-2xl font-semibold text-slate-900">

{{-- Título de Seção --}}
<h2 class="text-lg font-semibold text-slate-900">

{{-- Título de Card --}}
<h3 class="text-base font-semibold text-slate-900">

{{-- Subtítulo --}}
<p class="text-sm text-slate-500">
```

### Texto de Corpo
```blade
{{-- Texto normal --}}
<p class="text-sm text-slate-600">

{{-- Texto pequeno (hints) --}}
<p class="text-xs text-slate-500">

{{-- Label de formulário --}}
<label class="text-sm font-medium text-slate-700">
```

---

## 🧩 Componentes

### 1. Cards Estatísticos

```blade
<x-super.stat-card 
    label="Total de Empresas" 
    :value="$count"
    icon-bg="bg-blue-50" 
    icon-color="text-blue-700">
    <x-slot:icon>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <!-- ícone SVG -->
        </svg>
    </x-slot:icon>
</x-super.stat-card>
```

**Cores para ícones:**
- Azul (`bg-blue-50 text-blue-700`): Empresas, Geral
- Verde (`bg-emerald-50 text-emerald-700`): Ativo, Sucesso
- Roxo (`bg-violet-50 text-violet-700`): Usuários, Pessoas
- Âmbar (`bg-amber-50 text-amber-700`): Estatísticas, Movimentos

### 2. Cards de Conteúdo

```blade
<div class="super-card">
    <x-super.card-header 
        title="Título do Card" 
        description="Descrição opcional"
    />
    <div class="super-card-body">
        <!-- Conteúdo -->
    </div>
</div>
```

### 3. Badges de Status

```blade
{{-- Ativo --}}
<span class="super-badge super-badge-green">Ativo</span>

{{-- Inativo --}}
<span class="super-badge super-badge-gray">Inativo</span>

{{-- Informação --}}
<span class="super-badge super-badge-blue">Info</span>
```

### 4. Botões

```blade
{{-- Primário (ações principais) --}}
<button class="super-btn-primary">
    <svg class="w-4 h-4"><!-- ícone --></svg>
    Salvar
</button>

{{-- Secundário (ações alternativas) --}}
<button class="super-btn-secondary">
    Cancelar
</button>

{{-- Ghost (ações terciárias) --}}
<button class="super-btn-ghost">
    <svg class="w-4 h-4"><!-- ícone --></svg>
    Editar
</button>

{{-- Perigo (ações destrutivas) --}}
<button class="super-btn-danger">
    Desativar
</button>

{{-- Sucesso --}}
<button class="super-btn-success">
    Ativar
</button>
```

### 5. Formulários

```blade
<form wire:submit="save" class="space-y-5">
    <div>
        <label class="super-label">Nome do campo</label>
        <input type="text" class="super-input" placeholder="Placeholder">
        @error('field') 
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
        @enderror
    </div>
</form>
```

### 6. Estado Vazio

```blade
<x-super.empty-state 
    title="Nenhum item encontrado" 
    description="Descrição opcional"
>
    <x-slot:icon>
        <svg class="w-8 h-8"><!-- ícone --></svg>
    </x-slot:icon>
</x-super.empty-state>
```

### 7. Alertas

```blade
{{-- Sucesso --}}
<div class="flex items-start gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
    <svg class="w-5 h-5 shrink-0 mt-0.5"><!-- ícone check --></svg>
    <span>Mensagem de sucesso</span>
</div>

{{-- Erro --}}
<div class="flex items-start gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
    <svg class="w-5 h-5 shrink-0 mt-0.5"><!-- ícone alerta --></svg>
    <span>Mensagem de erro</span>
</div>
```

---

## 📐 Layouts

### Grid Responsivo (Formulário + Lista)

```blade
<div class="grid grid-cols-1 2xl:grid-cols-12 gap-6">
    {{-- Formulário --}}
    <div class="2xl:col-span-5">
        <div class="super-card sticky top-24">
            <!-- Formulário aqui -->
        </div>
    </div>

    {{-- Listagem --}}
    <div class="2xl:col-span-7 space-y-6">
        <!-- Lista aqui -->
    </div>
</div>
```

### Grid de Estatísticas

```blade
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
    <!-- Cards estatísticos -->
</div>
```

### Grid de Conteúdo 2 Colunas

```blade
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <!-- Conteúdo -->
</div>
```

---

## 📱 Responsividade

### Breakpoints
- **sm**: 640px (mobile grande)
- **md**: 768px (tablet)
- **lg**: 1024px (desktop pequeno)
- **xl**: 1280px (desktop)
- **2xl**: 1536px (desktop grande)

### Padrões Responsivos

```blade
{{-- Padding responsivo --}}
<div class="p-4 sm:p-6">

{{-- Texto responsivo --}}
<h1 class="text-xl sm:text-2xl">

{{-- Grid responsivo --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">

{{-- Flex responsivo --}}
<div class="flex flex-col sm:flex-row gap-4">
```

---

## 🎯 Boas Práticas

### Espaçamento
- Use `gap-4` ou `gap-6` para grids
- Use `space-y-4` ou `space-y-6` para listas verticais
- Use `p-4 sm:p-6` para padding de cards

### Arredondamento
- Cards principais: `rounded-xl`
- Elementos internos: `rounded-lg`
- Badges e pills: `rounded-md` ou `rounded-full`

### Sombras
- Cards normais: `shadow-sm`
- Cards com hover: `hover:shadow-md`
- Não use sombras muito pesadas

### Transições
- Sempre adicione `transition` para elementos interativos
- Para sombras: `transition-shadow`
- Para cores: `transition-colors`

### Ícones
- Tamanho padrão em botões: `w-4 h-4`
- Tamanho em cards estatísticos: `w-6 h-6`
- Tamanho em empty states: `w-8 h-8`
- Stroke padrão: `stroke-width="1.75"`

---

## 📦 Classes CSS Customizadas

### Navegação
```css
.super-nav-link          /* Link de navegação padrão */
.super-nav-link-active   /* Link ativo */
```

### Cards
```css
.super-card       /* Card base */
.super-card-body  /* Corpo do card com padding */
```

### Botões
```css
.super-btn-primary    /* Azul, ação principal */
.super-btn-secondary  /* Cinza, ação secundária */
.super-btn-ghost      /* Transparente, ação terciária */
.super-btn-danger     /* Vermelho, ação destrutiva */
.super-btn-success    /* Verde, ação de confirmação */
```

### Formulários
```css
.super-label  /* Label de campo */
.super-input  /* Input de texto */
```

### Badges
```css
.super-badge       /* Badge base */
.super-badge-blue  /* Badge azul */
.super-badge-green /* Badge verde */
.super-badge-gray  /* Badge cinza */
```

---

## 🔄 Aplicando em Novas Views

### Template Base

```blade
<x-layouts.super title="Título" subtitle="Subtítulo">
    {{-- Grid de estatísticas (opcional) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <x-super.stat-card label="Métrica" :value="$value" />
    </div>

    {{-- Grid principal: Formulário + Lista --}}
    <div class="grid grid-cols-1 2xl:grid-cols-12 gap-6">
        {{-- Formulário --}}
        <div class="2xl:col-span-5">
            <div class="super-card sticky top-24">
                <x-super.card-header title="Novo Item" />
                <div class="super-card-body">
                    <form wire:submit="save" class="space-y-5">
                        <!-- Campos -->
                    </form>
                </div>
            </div>
        </div>

        {{-- Listagem --}}
        <div class="2xl:col-span-7 space-y-6">
            <x-super.section-title title="Itens" />
            <div class="space-y-4">
                @forelse($items as $item)
                    <div class="super-card">
                        <!-- Item -->
                    </div>
                @empty
                    <div class="super-card">
                        <x-super.empty-state title="Vazio" />
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.super>
```

---

## ✅ Checklist de Qualidade

Antes de finalizar uma view, verifique:

- [ ] Responsiva em mobile, tablet e desktop
- [ ] Espaçamento consistente (múltiplos de 4px)
- [ ] Cores seguem a paleta definida
- [ ] Ícones com tamanho e stroke corretos
- [ ] Transições em elementos interativos
- [ ] Estados vazios implementados
- [ ] Mensagens de erro nos formulários
- [ ] Badges de status adequados
- [ ] Textos com hierarquia correta
- [ ] Acessibilidade (labels, alt text)

---

**Última atualização:** 11/08/2026  
**Versão:** 1.0
