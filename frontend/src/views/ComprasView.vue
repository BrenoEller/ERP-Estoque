<template>
  <div class="page">
    <div class="card">
      <div class="card-header">
        <div>
          <h1>Compras</h1>
        </div>

        <button class="btn-primary" @click="abrirModal">
          Nova compra
        </button>
      </div>

      <div class="card-body">
        <p v-if="mensagem" class="mensagem">{{ mensagem }}</p>

        <h2 class="section-title">Compras registradas</h2>

        <section v-if="compras.length" class="cards-grid">
          <article v-for="c in compras" :key="c.id" class="compra-card">
            <h2 class="compra-nome">Compra #{{ c.id }}</h2>

            <div class="compra-conteudo">
              <div class="linha">
                <span class="label">Fornecedor:</span>
                <span class="valor">{{ c.fornecedor }}</span>
              </div>

              <div class="linha">
                <span class="label">Data:</span>
                <span class="valor">{{ formatarData(c.created_at) }}</span>
              </div>

              <div class="linha">
                <span class="label">Total:</span>
                <span class="valor destaque">R$ {{ formatarNumero(c.total_compra) }}</span>
              </div>

              <div class="linha">
                <span class="label">Itens:</span>
                <span class="badge-estoque">{{ c.itens.length }} un.</span>
              </div>
            </div>

            <details class="detalhes-itens">
              <summary>Ver itens</summary>

              <ul>
                <li v-for="item in c.itens" :key="item.id">
                  {{ item.produto_nome ?? ('Produto #' + item.produto_id) }} —
                  {{ item.quantidade }} x R$ {{ formatarNumero(item.preco_unitario) }}
                  (Total: R$ {{ formatarNumero(item.total_cost) }})
                </li>
              </ul>
            </details>
          </article>
        </section>

        <p v-else class="hint">
          Nenhuma compra registrada ainda.
        </p>
      </div>
    </div>

    <div v-if="mostrarModal" class="modal-backdrop">
      <div class="modal">
        <div class="modal-header">
          <h2>Nova compra</h2>
          <button class="close" type="button" @click="fecharModal">×</button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label for="fornecedor">Fornecedor</label>
            <input
              id="fornecedor"
              v-model="form.fornecedor"
              type="text"
              placeholder="Ex: Fornecedor X"
            />
            <small v-if="errors.fornecedor" class="error">
              {{ errors.fornecedor }}
            </small>
          </div>

          <h3>Itens da compra</h3>

          <table class="tabela itens-table">
            <thead>
              <tr>
                <th>Produto</th>
                <th style="width: 120px;">Qtd</th>
                <th style="width: 150px;">Preço unit. (R$)</th>
                <th style="width: 60px;"></th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="(item, index) in form.itens" :key="index">
                <td>
                  <select v-model.number="item.produto_id">
                    <option value="">Selecione...</option>
                    <option v-for="p in produtos" :key="p.id" :value="p.id">
                      {{ p.nome }}
                    </option>
                  </select>
                  <small v-if="errors.itens[index]?.produto_id" class="error">
                    {{ errors.itens[index]?.produto_id }}
                  </small>
                </td>

                <td>
                  <input
                    v-model.number="item.quantidade"
                    type="number"
                    min="0"
                    step="0.01"
                    class="inputModal"
                  />
                  <small v-if="errors.itens[index]?.quantidade" class="error">
                    {{ errors.itens[index]?.quantidade }}
                  </small>
                </td>

                <td>
                  <input
                    v-model.number="item.preco_unitario"
                    type="number"
                    min="0"
                    step="0.01"
                  />
                  <small v-if="errors.itens[index]?.preco_unitario" class="error">
                    {{ errors.itens[index]?.preco_unitario }}
                  </small>
                </td>
              </tr>
            </tbody>
          </table>

          <button type="button" class="btn-secondary" @click="adicionarItem">
            + Adicionar item
          </button>

          <p v-if="mensagemModal" class="mensagem mensagem-modal">
            {{ mensagemModal }}
          </p>
        </div>

        <div class="modal-footer">

          <button
            class="btn-primary"
            type="button"
            :disabled="salvando"
            @click="salvarCompra"
          >
            {{ salvando ? 'Salvando...' : 'Salvar compra' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface Produto {
  id: number
  nome: string
}

interface CompraItemForm {
  produto_id: number | null
  quantidade: number | null
  preco_unitario: number | null
}

interface CompraForm {
  fornecedor: string
  itens: CompraItemForm[]
}

interface ItemError {
  produto_id?: string
  quantidade?: string
  preco_unitario?: string
}

interface FormErrors {
  fornecedor?: string
  itens: ItemError[]
}

interface CompraItemLista {
  id: number
  produto_id: number
  produto_nome: string | null
  quantidade: number
  preco_unitario: number
  total_cost: number
}

interface CompraLista {
  id: number
  fornecedor: string
  total_compra: number
  created_at: string | null
  itens: CompraItemLista[]
}

const API_URL: string = (import.meta as any).env.VITE_API_URL ?? 'http://localhost:8000'

const produtos = ref<Produto[]>([])
const compras = ref<CompraLista[]>([])

const mostrarModal = ref(false)
const salvando = ref(false)
const mensagem = ref('')
const mensagemModal = ref('')

const form = ref<CompraForm>({
  fornecedor: '',
  itens: [
    {
      produto_id: null,
      quantidade: null,
      preco_unitario: null,
    },
  ],
})

const errors = ref<FormErrors>({
  fornecedor: undefined,
  itens: [{}],
})

function limparErros(): void {
  errors.value = {
    fornecedor: undefined,
    itens: form.value.itens.map(() => ({})),
  }
  mensagemModal.value = ''
}

function abrirModal(): void {
  limparErros()
  form.value = {
    fornecedor: '',
    itens: [
      {
        produto_id: null,
        quantidade: null,
        preco_unitario: null,
      },
    ],
  }
  mostrarModal.value = true
}

function fecharModal(): void {
  mostrarModal.value = false
}

function adicionarItem(): void {
  form.value.itens.push({
    produto_id: null,
    quantidade: null,
    preco_unitario: null,
  })
  errors.value.itens.push({})
}

function removerItem(index: number): void {
  if (form.value.itens.length === 1) return
  form.value.itens.splice(index, 1)
  errors.value.itens.splice(index, 1)
}

function validarForm(): boolean {
  limparErros()
  let ok = true

  if (!form.value.fornecedor || form.value.fornecedor.length < 3) {
    errors.value.fornecedor = 'Informe um fornecedor com pelo menos 3 caracteres.'
    ok = false
  }

  if (!form.value.itens.length) {
    mensagemModal.value = 'Adicione pelo menos um item.'
    return false
  }

  form.value.itens.forEach((item, index) => {
    const e: ItemError = {}

    if (!item.produto_id) {
      e.produto_id = 'Selecione um produto.'
      ok = false
    }

    if (item.quantidade == null || item.quantidade <= 0) {
      e.quantidade = 'Quantidade deve ser maior que zero.'
      ok = false
    }

    if (item.preco_unitario == null || item.preco_unitario <= 0) {
      e.preco_unitario = 'Preço deve ser maior que zero.'
      ok = false
    }

    errors.value.itens[index] = e
  })

  return ok
}

async function carregarProdutos(): Promise<void> {
  try {
    const resp = await fetch(`${API_URL}/produtos`)
    if (!resp.ok) throw new Error('Erro ao carregar produtos')
    const data = (await resp.json()) as any[]
    produtos.value = data.map(p => ({
      id: p.id,
      nome: p.nome,
    }))
  } catch (e) {
    mensagem.value = 'Erro ao carregar produtos para a compra.'
  }
}

async function carregarCompras(): Promise<void> {
  try {
    const resp = await fetch(`${API_URL}/compras`)
    if (!resp.ok) throw new Error('Erro ao carregar compras')
    const data = (await resp.json()) as any[]
    compras.value = data as CompraLista[]
  } catch (e) {
    mensagem.value = 'Erro ao carregar compras.'
  }
}

function formatarNumero(valor: number | string | null | undefined): string {
  if (valor == null) return '0,00'
  const num = typeof valor === 'string' ? Number(valor) : valor
  if (Number.isNaN(num)) return '0,00'
  return num.toFixed(2).replace('.', ',')
}

function formatarData(valor: string | null): string {
  if (!valor) return '-'
  const partes = valor.replace(' ', 'T')
  const date = new Date(partes)
  if (Number.isNaN(date.getTime())) return valor
  return date.toLocaleString('pt-BR')
}

async function salvarCompra(): Promise<void> {
  if (!validarForm()) return

  salvando.value = true
  mensagemModal.value = ''
  mensagem.value = ''

  try {
    const resp = await fetch(`${API_URL}/compras`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        fornecedor: form.value.fornecedor,
        produtos: form.value.itens.map(i => ({
          id: i.produto_id,
          quantidade: i.quantidade,
          preco_unitario: i.preco_unitario,
        })),
      }),
    })

    const contentType = resp.headers.get('content-type') || ''
    let data: any = null

    if (contentType.includes('application/json')) {
      data = await resp.json()
    } else {
      mensagemModal.value = 'Erro inesperado ao registrar compra.'
      return
    }

    if (!resp.ok) {
      if (data?.errors) {
        if (data.errors.fornecedor?.[0]) {
          errors.value.fornecedor = data.errors.fornecedor[0]
        }
        mensagemModal.value = data.message ?? 'Erro de validação na compra.'
      } else {
        mensagemModal.value = data?.message ?? 'Erro ao registrar compra.'
      }
      return
    }

    mensagem.value = 'Compra registrada com sucesso!'
    fecharModal()
    await carregarCompras()
  } catch (e) {
    mensagemModal.value = 'Erro de comunicação com o servidor.'
  } finally {
    salvando.value = false
  }
}

onMounted(() => {
  void carregarProdutos()
  void carregarCompras()
})
</script>

<style scoped>
.page {
  padding: 1.5rem;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.card {
  max-width: 1100px;
  margin: 0 auto;
  border-radius: 12px;
  padding: 1.5rem;
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}

.card-body {
  margin-top: 0.5rem;
}

.subtitle {
  margin: 0.25rem 0 0;
  font-size: 0.9rem;
  color: #64748b;
}

.section-title {
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

.hint {
  font-size: 0.9rem;
  color: #94a3b8;
}

.btn-primary {
  padding: 0.6rem 1.3rem;
  border-radius: 999px;
  border: none;
  background: #0d99ff;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.25rem;
  margin-top: 1rem;
}

.compra-card {
  background: #ffffff;
  border-radius: 10px;
  padding: 1.25rem 1.5rem;
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.compra-nome {
  font-size: 1.05rem;
  font-weight: 700;
  color: #1f2933;
  margin: 0 0 0.25rem;
}

.compra-conteudo {
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

.detalhes-itens summary {
  cursor: pointer;
  color: #0d99ff;
  font-weight: 600;
  margin-top: 0.5rem;
}

.detalhes-itens ul {
  margin-top: 0.5rem;
  padding-left: 1.1rem;
  font-size: 0.85rem;
  color: #374151;
}

.empty-text {
  margin-top: 1rem;
  text-align: center;
  color: #6b7280;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.modal {
  width: 100%;
  max-width: 720px;
  background: #fff;
  border-radius: 14px;
  padding: 1.5rem;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.modal-body {
  max-height: 60vh;
  overflow-y: auto;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1rem;
}

.close {
  border: none;
  background: transparent;
  font-size: 1.3rem;
  cursor: pointer;
}

.form-group {
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

input,
select {
  width: 100%;
  padding: 0.45rem 0.6rem;
  border-radius: 6px;
  border: 1px solid #cbd5e1;
  font-size: 0.9rem;
}

.tabela {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

.btn-secondary {
  margin-top: 0.75rem;
  padding: 0.4rem 0.9rem;
  border-radius: 999px;
  border: 1px dashed #0d99ff;
  background: #eff6ff;
  color: #0d99ff;
  cursor: pointer;
  font-size: 0.85rem;
}

.tabela th,
.tabela td {
  border-bottom: 1px solid #e2e8f0;
  padding: 0.45rem 0.4rem;
  vertical-align: top;
}

.tabela th {
  text-align: left;
  background: #f8fafc;
  font-weight: 600;
  color: #475569;
}

.error {
  color: #dc2626;
  font-size: 0.75rem;
}

.inputModal {
    width: 80% !important;
}

@media (max-width: 640px) {
    .modal {
        width: 80%;
    }
}
</style>
