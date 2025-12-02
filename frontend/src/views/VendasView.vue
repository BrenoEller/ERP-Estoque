<template>
  <div class="page">
    <div class="card">
      <div class="card-header">
        <div>
          <h1>Vendas</h1>
        </div>

        <button class="btn-primary" @click="abrirModal">
          Nova venda
        </button>
      </div>

      <div class="card-body">
        <p v-if="mensagem" class="mensagem">{{ mensagem }}</p>

        <h2 class="section-title">Vendas registradas</h2>

        <section v-if="vendas.length" class="cards-grid">
          <article v-for="v in vendas" :key="v.id" class="compra-card">
            <h2 class="compra-nome">Venda #{{ v.id }}</h2>

            <div class="compra-conteudo">
              <div class="linha">
                <span class="label">Cliente:</span>
                <span class="valor">{{ v.cliente }}</span>
              </div>

              <div class="linha">
                <span class="label">Data:</span>
                <span class="valor">{{ formatarData(v.sale_date) }}</span>
              </div>

              <div class="linha">
                <span class="label">Status:</span>
                <span class="badge-status" :data-status="v.status">
                  {{ v.status === 'cancelado' ? 'Cancelada' : 'Ativa' }}
                </span>
              </div>

              <div class="linha">
                <span class="label">Total venda:</span>
                <span class="valor destaque">R$ {{ formatarNumero(v.total_venda) }}</span>
              </div>

              <div class="linha">
                <span class="label">Lucro:</span>
                <span class="valor destaque-lucro">R$ {{ formatarNumero(v.lucro_total) }}</span>
              </div>

              <div class="linha">
                <span class="label">Itens:</span>
                <span class="badge-estoque">{{ v.itens.length }} un.</span>
              </div>
            </div>

            <details class="detalhes-itens">
              <summary>Ver itens</summary>

              <ul>
                <li v-for="item in v.itens" :key="item.id">
                  {{ item.produto_nome ?? ('Produto #' + item.produto_id) }} —
                  {{ item.quantidade }} x R$ {{ formatarNumero(item.preco_unitario) }}
                  (Total: R$ {{ formatarNumero(item.total_price) }} | Lucro: R$ {{ formatarNumero(item.profit) }})
                </li>
              </ul>
            </details>

            <div class="card-actions">
              <button
                v-if="v.status === 'ativo'"
                type="button"
                class="btn-cancel"
                @click="cancelarVenda(v.id)"
                :disabled="salvando"
              >
                Cancelar venda
              </button>
            </div>
          </article>
        </section>

        <p v-else class="hint">
          Nenhuma venda registrada ainda.
        </p>
      </div>
    </div>

    <div v-if="mostrarModal" class="modal-backdrop">
      <div class="modal">
        <div class="modal-header">
          <h2>Nova venda</h2>
          <button class="close" type="button" @click="fecharModal">×</button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label for="cliente">Cliente</label>
            <input
              id="cliente"
              v-model="form.cliente"
              type="text"
              placeholder="Ex: João da Silva"
            />
            <small v-if="errors.cliente" class="error">
              {{ errors.cliente }}
            </small>
          </div>

          <div class="info-row">
            <div>
              <span class="label-inline">Total da venda:</span>
              <span class="valor-inline">
                R$ {{ formatarNumero(totalVenda) }}
              </span>
            </div>
            <div>
              <span class="label-inline">Lucro estimado:</span>
              <span class="valor-inline destaque-lucro">
                R$ {{ formatarNumero(totalLucro) }}
              </span>
            </div>
          </div>

          <h3>Itens da venda</h3>

          <table class="tabela itens-table">
            <thead>
              <tr>
                <th>Produto</th>
                <th style="width: 90px;">Estoque</th>
                <th style="width: 110px;">Qtd</th>
                <th style="width: 130px;">Preço venda</th>
                <th style="width: 130px;">Subtotal</th>
                <th style="width: 130px;">Lucro</th>
                <th style="width: 60px;"></th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="(item, index) in itens" :key="index">
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

                <td class="text-center">
                  <span v-if="produtoPorId(item.produto_id)">
                    {{ produtoPorId(item.produto_id)?.estoque_atual }} un.
                  </span>
                  <span v-else>–</span>
                </td>

                <td>
                  <input
                    v-model.number="item.quantidade"
                    type="number"
                    min="1"
                    step="1"
                  />
                  <small v-if="errors.itens[index]?.quantidade" class="error">
                    {{ errors.itens[index]?.quantidade }}
                  </small>
                </td>

                <td>
                  <span v-if="produtoPorId(item.produto_id)">
                    R$
                    {{ formatarNumero(produtoPorId(item.produto_id)?.preco_venda ?? 0) }}
                  </span>
                  <span v-else>–</span>
                </td>

                <td>
                  <span>
                    R$ {{ formatarNumero(subtotalItem(item)) }}
                  </span>
                </td>

                <td>
                  <span class="lucro-item">
                    R$ {{ formatarNumero(lucroItem(item)) }}
                  </span>
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
            @click="salvarVenda"
          >
            {{ salvando ? 'Salvando...' : 'Registrar venda' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

interface Produto {
  id: number
  nome: string
  custo_medio: number | string
  preco_venda: number | string
  estoque_atual: number
}

interface VendaItemForm {
  produto_id: number | null
  quantidade: number | null
}

interface ItemError {
  produto_id?: string
  quantidade?: string
}

interface FormErrors {
  cliente?: string
  itens: ItemError[]
}

interface VendaForm {
  cliente: string
}

interface VendaItemLista {
  id: number
  produto_id: number
  produto_nome: string | null
  quantidade: number
  preco_unitario: number
  total_price: number
  cost_at_sale: number
  total_cost: number
  profit: number
}

interface VendaLista {
  id: number
  cliente: string
  status: string
  sale_date: string | null
  total_venda: number
  lucro_total: number
  itens: VendaItemLista[]
}

const API_URL: string = (import.meta as any).env.VITE_API_URL ?? 'http://localhost:8000'

const produtos = ref<Produto[]>([])
const vendas = ref<VendaLista[]>([])
const itens = ref<VendaItemForm[]>([
  {
    produto_id: null,
    quantidade: null,
  },
])

const form = ref<VendaForm>({
  cliente: '',
})

const errors = ref<FormErrors>({
  cliente: undefined,
  itens: [{}],
})

const mostrarModal = ref(false)
const salvando = ref(false)
const mensagem = ref('')
const mensagemModal = ref('')

function limparErros(): void {
  errors.value = {
    cliente: undefined,
    itens: itens.value.map(() => ({})),
  }
  mensagemModal.value = ''
}

function abrirModal(): void {
  limparErros()
  form.value = {
    cliente: '',
  }
  itens.value = [
    {
      produto_id: null,
      quantidade: null,
    },
  ]
  mostrarModal.value = true
}

function fecharModal(): void {
  mostrarModal.value = false
}

function adicionarItem(): void {
  itens.value.push({
    produto_id: null,
    quantidade: null,
  })
  errors.value.itens.push({})
}

function removerItem(index: number): void {
  if (itens.value.length === 1) return
  itens.value.splice(index, 1)
  errors.value.itens.splice(index, 1)
}

function produtoPorId(id: number | null): Produto | undefined {
  if (id == null) return undefined
  return produtos.value.find(p => p.id === id)
}

function validarForm(): boolean {
  limparErros()
  let ok = true

  if (!form.value.cliente || form.value.cliente.length < 3) {
    errors.value.cliente = 'Informe um cliente com pelo menos 3 caracteres.'
    ok = false
  }

  if (!itens.value.length) {
    mensagemModal.value = 'Adicione pelo menos um item.'
    return false
  }

  itens.value.forEach((item, index) => {
    const e: ItemError = {}

    if (!item.produto_id) {
      e.produto_id = 'Selecione um produto.'
      ok = false
    }

    if (item.quantidade == null || item.quantidade <= 0) {
      e.quantidade = 'Quantidade deve ser maior que zero.'
      ok = false
    } else {
      const produto = produtoPorId(item.produto_id)
      if (produto && item.quantidade > produto.estoque_atual) {
        e.quantidade = 'Quantidade não pode exceder o estoque atual.'
        ok = false
      }
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
    produtos.value = data as Produto[]
  } catch (e) {
    mensagem.value = 'Erro ao carregar produtos para venda.'
  }
}

async function carregarVendas(): Promise<void> {
  try {
    const resp = await fetch(`${API_URL}/vendas`)
    if (!resp.ok) throw new Error('Erro ao carregar vendas')
    const data = (await resp.json()) as any[]
    vendas.value = data as VendaLista[]
  } catch (e) {
    mensagem.value = 'Erro ao carregar vendas.'
  }
}

function numero(valor: number | string | null | undefined): number {
  if (valor == null) return 0
  if (typeof valor === 'number') return valor
  const n = Number(valor)
  return Number.isNaN(n) ? 0 : n
}

function subtotalItem(item: VendaItemForm): number {
  const produto = produtoPorId(item.produto_id)
  if (!produto || item.quantidade == null || item.quantidade <= 0) return 0
  const preco = numero(produto.preco_venda)
  return item.quantidade * preco
}

function lucroItem(item: VendaItemForm): number {
  const produto = produtoPorId(item.produto_id)
  if (!produto || item.quantidade == null || item.quantidade <= 0) return 0
  const preco = numero(produto.preco_venda)
  const custo = numero(produto.custo_medio)
  const margem = preco - custo
  return item.quantidade * margem
}

const totalVenda = computed<number>(() => {
  return itens.value.reduce((acc, item) => acc + subtotalItem(item), 0)
})

const totalLucro = computed<number>(() => {
  return itens.value.reduce((acc, item) => acc + lucroItem(item), 0)
})

function formatarNumero(valor: number | string | null | undefined): string {
  const n = typeof valor === 'number' ? valor : numero(valor)
  return n.toFixed(2).replace('.', ',')
}

function formatarData(valor: string | null): string {
  if (!valor) return '-'
  const partes = valor.replace(' ', 'T')
  const date = new Date(partes)
  if (Number.isNaN(date.getTime())) return valor
  return date.toLocaleString('pt-BR')
}

async function salvarVenda(): Promise<void> {
  if (!validarForm()) return

  salvando.value = true
  mensagemModal.value = ''
  mensagem.value = ''

  try {
    const payloadProdutos = itens.value.map(i => {
      const produto = produtoPorId(i.produto_id)
      return {
        id: i.produto_id,
        quantidade: i.quantidade,
        preco_unitario: numero(produto?.preco_venda ?? 0),
      }
    })

    const resp = await fetch(`${API_URL}/vendas`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        cliente: form.value.cliente,
        produtos: payloadProdutos,
      }),
    })

    const contentType = resp.headers.get('content-type') || ''
    let data: any = null

    if (contentType.includes('application/json')) {
      data = await resp.json()
    }

    if (!resp.ok) {
      if (data?.errors) {
        if (data.errors.cliente?.[0]) {
          errors.value.cliente = data.errors.cliente[0]
        }
        mensagemModal.value = data.message ?? 'Erro ao registrar venda.'
      } else if (data?.message) {
        mensagemModal.value = data.message
      } else {
        mensagemModal.value = 'Erro ao registrar venda.'
      }
      return
    }

    mensagem.value = 'Venda registrado com sucesso!'
    fecharModal()
    await carregarProdutos()
    await carregarVendas()
  } catch (e) {
    mensagemModal.value = 'Erro de comunicação com o servidor.'
  } finally {
    salvando.value = false
  }
}

async function cancelarVenda(id: number): Promise<void> {
  salvando.value = true
  mensagemModal.value = ''
  mensagem.value = ''

  try {
    const resp = await fetch(`${API_URL}/vendas/${id}/cancelar`, {
      method: 'POST',
    })

    const contentType = resp.headers.get('content-type') || ''
    let data: any = null

    if (contentType.includes('application/json')) {
      data = await resp.json()
    }

    if (!resp.ok) {
      if (data?.message) {
        mensagem.value = data.message
      } else {
        mensagem.value = 'Erro ao cancelar venda.'
      }
      return
    }

    mensagem.value = data?.message ?? 'Venda cancelada com sucesso!'
    await carregarProdutos()
    await carregarVendas()
  } catch (e) {
    mensagem.value = 'Erro de comunicação com o servidor.'
  } finally {
    salvando.value = false
  }
}

onMounted(() => {
  void carregarProdutos()
  void carregarVendas()
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

.valor.destaque-lucro {
  color: #15803d;
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

.badge-status {
  display: inline-flex;
  align-items: center;
  padding: 0.15rem 0.6rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
}

.badge-status[data-status='ativo'] {
  background: #dcfce7;
  color: #166534;
}

.badge-status[data-status='cancelado'] {
  background: #fee2e2;
  color: #b91c1c;
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

.info-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.75rem;
  font-size: 0.9rem;
}

.label-inline {
  color: #64748b;
  margin-right: 0.25rem;
}

.valor-inline {
  font-weight: 600;
}

.valor-inline.destaque-lucro {
  color: #15803d;
}

.lucro-item {
  color: #15803d;
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

.btn-icon {
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 1rem;
}

.mensagem {
  margin-top: 0.5rem;
  font-size: 0.9rem;
}

.mensagem-modal {
  margin-top: 0.75rem;
}

.text-center {
  text-align: center;
}

.card-actions {
  margin-top: 0.75rem;
  display: flex;
  justify-content: flex-end;
}

.btn-cancel {
  padding: 0.45rem 1rem;
  border-radius: 999px;
  border: 1px solid #fecaca;
  background: #fee2e2;
  color: #b91c1c;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
}

@media (max-width: 640px) {
    .modal {
        width: 80%;
    }
}
</style>
