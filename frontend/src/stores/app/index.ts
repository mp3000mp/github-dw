import { defineStore } from 'pinia'
import { state } from './state'
import apiRegistry from '@/helpers/apiRegistry'

// todo: define actions in separate file when thie issue is closed: https://github.com/vuejs/pinia/issues/802
export const useAppStore = defineStore('app', {
  state: () => state,
  actions: {
    async getInfo() {
      const responseJson = await apiRegistry
        .get()
        .httpReq<{ version: string }>(this.actionRequests.getInfo)
      this.version = responseJson.version
    }
  }
})
