import { AbstractState } from '@/stores/types'

export enum LanguageColorEnum {
  Go = '#00a7d0',
  Javascript = '#efd81d',
  PHP = '#7377ad',
  Python = '#3571a3'
}

export interface SelectOption {
  id: number
  name: string
}
interface Topic {
  id: number
  topic: string
}
export interface Repository {
  id: number
  name: string
  username: string
  mainLanguage: string
  url: string
  fullName: string | null
  description: string | null
  licenceName: string
  forksCount: number
  openIssuesCount: number
  stargazersCount: number
  createdAt: string
  pushedAt: string
  topics: Topic[]
}
interface SearchPackage {
  id: number
  minVersion: string | null
  maxVersion: string | null
}
export interface Dependency {
  idx: number
  language: string
  name: string | null
  id: number
  minVersion: string | null
  maxVersion: string | null
}
export interface SearchQuery {
  page: number
  perPage: number
  search: Search
}
export interface Search {
  name: string | null
  description: string | null
  packages: SearchPackage[]
}

export class SearchState extends AbstractState {
  packageOptions: SelectOption[] = []
  search: Search = {
    name: null,
    description: null,
    packages: []
  }
  totalRepositories = 0
  repositories: Repository[] = []
}
