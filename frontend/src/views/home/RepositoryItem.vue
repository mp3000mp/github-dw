<script lang="ts" setup>
import dayjs from 'dayjs'
import {Repository} from '@/stores/search/types'
import tooltipPopper from 'vue3-popper'

defineProps<{
  repository: Repository;
}>()
</script>

<template>
  <div class="card">
    <div class="card-header p-3">
      <div class="row">
        <h3 class="col-md-6 mb-2 mb-md-0"><a :href="repository.url" target="_blank">{{ repository.fullName }}</a></h3>
        <div class="col-md-6 text-md-end">
                <span class="mr-2">
                  <tooltip-popper content="License" :hover="true" :arrow="true">
                    <span>
                      <font-awesome icon="scale-balanced" /> {{ repository.licenceName ?? 'Unknown' }}
                    </span>
                  </tooltip-popper>
                </span>
          <span class="ml-2">
                  <tooltip-popper content="Last pushed at" :hover="true" :arrow="true">
                    <span>
                      <font-awesome icon="clock-rotate-left" /> {{ dayjs(repository.pushedAt).format('YYYY-MM-DD') }}
                    </span>
                  </tooltip-popper>
                </span>
        </div>
      </div>
    </div>
    <div class="card-body p-3 fs-09">
      <p><strong>Description</strong>: {{ repository.description }}</p>
      <div v-if="repository.topics.length > 0">
        Topics: <span class="badge" v-for="topic in repository.topics" :key="topic.topic">{{ topic.topic }}</span>
      </div>
    </div>
    <div class="card-footer p-3">
          <span class="mx-2">
            <font-awesome icon="star" /> {{ repository.stargazersCount ?? 0 }} stars
          </span>
      <span class="mx-2">
            <font-awesome icon="code-fork" /> {{ repository.forksCount ?? 0 }} forks
          </span>
      <span class="mx-2">
              <tooltip-popper content="Open issues" :hover="true" :arrow="true">
                <span>
                  <font-awesome icon="circle-dot" /> {{ repository.openIssuesCount ?? 0 }} issues
                </span>
              </tooltip-popper>
            </span>
    </div>
  </div>
</template>
