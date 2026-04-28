import { defineStore } from 'pinia'
import { state } from './state'
import apiRegistry from '@/helpers/apiRegistry'

// todo: define actions in separate file when thie issue is closed: https://github.com/vuejs/pinia/issues/802
export const useAdminStore = defineStore('admin', {
  state: () => state,
  actions: {
    async getAll() {
      this.packageTypeFiles = await apiRegistry.get().httpReq(this.actionRequests.getAll)
    },
    async getErrors() {
      this.errors = await apiRegistry.get().httpReq(this.actionRequests.getErrors)
    },
    async getStats() {
      this.stats = await apiRegistry.get().httpReq(this.actionRequests.getStats)
    },
    async getTimeline() {
      this.timeline = await apiRegistry.get().httpReq(this.actionRequests.getTimeline)
    },
    async setPriority(id: number) {
      this.packageTypeFiles = await apiRegistry
        .get()
        .httpReq(this.actionRequests.setPriority, { urlParams: { id: String(id) } })
    }
  }
})
