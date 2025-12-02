<template>
  <div class="page">
    <!-- Banner de sucesso -->
    <p v-if="mensagem" class="alert-success">
      {{ mensagem }}
    </p>

    <!-- Cabeçalho -->
    <header class="page-header">
      <h1>Cadastro de produtos</h1>

      <button type="button" class="btn-primary" @click="abrirModal">
        Novo produto
      </button>
    </header>

    <!-- Lista em cards -->
    <section v-if="produtos.length" class="cards-grid">
      <article
        v-for="p in produtos"
        :key="p.id"
        class="produto-card"
      >
        <h2 class="produto-nome">
          {{ p.nome }}
        </h2>

        <div class="produto-conteudo">
          <div class="linha">
            <span class="label">ID:</span>
            <span class="valor">#{{ p.id }}</span>
          </div>

          <div class="linha">
            <span class="label">Custo médio:</span>
            <span class="valor">R$ {{ formatarNumero(p.custo_medio) }}</span>
          </div>

          <div class="linha">
            <span class="label">Preço de venda:</span>
            <span class="valor destaque">
              R$ {{ formatarNumero(p.preco_venda) }}
            </span>
          </div>

          <div class="linha">
            <span class="label">Estoque atual:</span>
            <span class="badge-estoque">
              {{ p.estoque_atual }} un.
            </span>
          </div>
        </div>

        <div class="card-actions">
          <button
            type="button"
            class="btn-outline"
            @click="abrirModalEdicao(p)"
          >
            Editar
          </button>
        </div>
      </article>
    </section>

    <p v-else class="empty-text">
      Nenhum produto cadastrado ainda.
    </p>

    <!-- Modal -->
    <div v-if="mostrarModal" class="modal-backdrop" @click.self="fecharModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ editandoId ? 'Editar produto' : 'Novo produto' }}</h2>
          <button type="button" class="close-btn" @click="fecharModal">
            ×
          </button>
        </div>

        <form class="modal-body" @submit.prevent="salvarProduto">
          <div class="form-group">
            <label for="nome">Nome do produto</label>
            <input
              id="nome"
              v-model="form.nome"
              type="text"
              placeholder="Ex: Teclado Gamer"
            />
            <small v-if="errors.nome" class="error">{{ errors.nome }}</small>
          </div>

          <div class="form-group">
            <label for="preco_venda">Preço de venda (R$)</label>
            <input
              id="preco_venda"
              v-model.number="form.preco_venda"
              type="number"
              step="0.01"
              min="0"
              placeholder="Ex: 250.00"
            />
            <small v-if="errors.preco_venda" class="error">
              {{ errors.preco_venda }}
            </small>
          </div>

          <div class="modal-footer">
            <button
              type="button"
              class="btn-outline"
              @click="fecharModal"
              :disabled="loading"
            >
              Cancelar
            </button>

            <button
              type="submit"
              class="btn-primary"
              :disabled="loading"
            >
              {{ loading ? 'Salvando...' : (editandoId ? 'Salvar alterações' : 'Salvar produto') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface Product {
  id: number
  nome: string
  custo_medio: number | string
  preco_venda: number | string
  estoque_atual: number
}

interface ProductForm {
  nome: string
  preco_venda: number | null
}

interface FormErrors {
  nome?: string
  preco_venda?: string
}

const API_URL: string = (import.meta as any).env.VITE_API_URL ?? 'http://localhost:8000'

const produtos = ref<Product[]>([])
const loading = ref(false)
const mensagem = ref<string>('')

const form = ref<ProductForm>({
  nome: '',
  preco_venda: null,
})

const errors = ref<FormErrors>({})
const mostrarModal = ref(false)
const editandoId = ref<number | null>(null)

function limparErros(): void {
  errors.value = {}
}

function limparForm(): void {
  form.value = {
    nome: '',
    preco_venda: null,
  }
}

function validarForm(): boolean {
  limparErros()
  let ok = true

  if (!form.value.nome || form.value.nome.length < 3) {
    errors.value.nome = 'O nome deve ter pelo menos 3 caracteres.'
    ok = false
  }

  if (form.value.preco_venda == null || form.value.preco_venda <= 0) {
    errors.value.preco_venda = 'Informe um preço maior que zero.'
    ok = false
  }

  return ok
}

async function carregarProdutos(): Promise<void> {
  try {
    const resp = await fetch(`${API_URL}/produtos`)
    if (!resp.ok) throw new Error('Erro ao carregar produtos')

    const data = (await resp.json()) as Product[]
    produtos.value = data
  } catch (e) {
    console.error(e)
  }
}

function formatarNumero(valor: number | string | null | undefined): string {
  if (valor == null) return '0,00'
  const num = typeof valor === 'string' ? Number(valor) : valor
  if (Number.isNaN(num)) return '0,00'
  return num.toFixed(2).replace('.', ',')
}

function abrirModal(): void {
  editandoId.value = null
  limparForm()
  limparErros()
  mostrarModal.value = true
}

function abrirModalEdicao(produto: Product): void {
  editandoId.value = produto.id
  form.value = {
    nome: produto.nome,
    preco_venda: Number(produto.preco_venda),
  }
  limparErros()
  mostrarModal.value = true
}

function fecharModal(): void {
  mostrarModal.value = false
  editandoId.value = null
}

async function salvarProduto(): Promise<void> {
  if (!validarForm()) return

  loading.value = true
  mensagem.value = ''

  const isEdicao = editandoId.value !== null
  const url = isEdicao
    ? `${API_URL}/produtos/${editandoId.value}`
    : `${API_URL}/produtos`
  const method = isEdicao ? 'PUT' : 'POST'

  try {
    const resp = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        nome: form.value.nome,
        preco_venda: form.value.preco_venda,
      }),
    })

    const data: any = await resp.json()

    if (!resp.ok) {
      if (data?.errors) {
        if (data.errors.nome?.[0]) errors.value.nome = data.errors.nome[0]
        if (data.errors.preco_venda?.[0]) {
          errors.value.preco_venda = data.errors.preco_venda[0]
        }
      }
      return
    }

    const produto = data as Product

    if (isEdicao) {
      const idx = produtos.value.findIndex(p => p.id === editandoId.value)
      if (idx !== -1) produtos.value[idx] = produto
      mensagem.value = 'Produto atualizado com sucesso!'
    } else {
      produtos.value.push(produto)
      mensagem.value = 'Produto cadastrado com sucesso!'
    }

    fecharModal()
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  void carregarProdutos()
})
</script>

<style scoped>
.page {
  max-width: 1180px;
  margin: 2rem auto;
  padding: 1rem 1.5rem 3rem;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.page-header h1 {
  font-size: 1.8rem;
  font-weight: 700;
}

.alert-success {
  background: #c6f6d5;
  color: #22543d;
  border-radius: 6px;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
  text-align: center;
  font-size: 0.95rem;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.25rem;
}

.produto-card {
  background: #ffffff;
  border-radius: 10px;
  padding: 1.25rem 1.5rem;
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.produto-nome {
  font-size: 1.05rem;
  font-weight: 700;
  color: #1f2933;
  margin: 0 0 0.25rem;
}

.produto-conteudo {
  font-size: 0.9rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.linha {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.label {
  font-weight: 600;
  color: #4b5563;
}

.valor {
  color: #111827;
}

.valor.destaque {
  color: #0d99ff;
  font-weight: 700;
}

.badge-estoque {
  display: inline-flex;
  align-items: center;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  background: #e5f2ff;
  color: #0d66d0;
  font-weight: 600;
  font-size: 0.8rem;
}

.card-actions {
  margin-top: 0.5rem;
  display: flex;
  justify-content: flex-end;
}

.btn-primary {
  border: none;
  padding: 0.55rem 1.1rem;
  border-radius: 25px;
  background: #0d99ff;
  color: #ffffff;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.15s ease, transform 0.05s ease;
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: default;
}

.btn-primary:not(:disabled):hover {
  background: #0a7fd4;
}

.btn-primary:not(:disabled):active {
  transform: scale(0.97);
}

.btn-outline {
  padding: 0.45rem 1rem;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  background: #ffffff;
  color: #111827;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.15s ease, border-color 0.15s ease;
}

.btn-outline:hover {
  background: #f3f4f6;
  border-color: #9ca3af;
}

.empty-text {
  margin-top: 2rem;
  text-align: center;
  color: #6b7280;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.modal {
  width: 100%;
  max-width: 480px;
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.35);
  overflow: hidden;
  margin: 0px 20px;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.9rem 1.2rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
  font-size: 1.1rem;
  font-weight: 700;
}

.close-btn {
  border: none;
  background: transparent;
  font-size: 1.3rem;
  cursor: pointer;
  line-height: 1;
}

.modal-body {
  padding: 1.1rem 1.2rem 1.2rem;
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.form-group label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151;
}

.form-group input {
  padding: 0.5rem 0.6rem;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  font-size: 0.9rem;
}

.form-group input:focus {
  outline: none;
  border-color: #0d99ff;
  box-shadow: 0 0 0 1px rgba(13, 153, 255, 0.2);
}

.error {
  color: #e53e3e;
  font-size: 0.78rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 0.5rem;
}
</style>
