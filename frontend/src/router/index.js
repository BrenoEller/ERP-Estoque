import { createRouter, createWebHistory } from 'vue-router'
import ProdutosView from '../views/ProdutosView.vue'
import ComprasView from '../views/ComprasView.vue'
import VendasView from '../views/VendasView.vue'

const routes = [
  { path: '/', redirect: '/produtos' },

  { path: '/produtos', name: 'produtos', component: ProdutosView },
  { path: '/compras', name: 'compras', component: ComprasView },
  { path: '/vendas', name: 'vendas', component: VendasView },

  { path: '/:pathMatch(.*)*', redirect: '/produtos' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
