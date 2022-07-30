import { AbstractState } from '@/stores/types'

export interface Dependency {
    id: number;
    name: string;
}
interface Repository {
    id: number;
    name: string;
    username: string;
    mainLanguage: string;
    url: string;
    description: string;
    licenceName: string;
    forksCount: number;
    openIssuesCount: number;
    stargazersCount: number;
    createdAt: string;
    pushedAt: string;
}
interface SearchPackage {
    id: number,
    minVersion: string,
    maxVersion: string,
}
export interface Search {
    name: string;
    description: string;
    packages: SearchPackage[];
}

export class SearchState extends AbstractState {
    packages: Dependency[] = [];
    search: Search = {
        name: '',
        description: '',
        packages: [],
    };
    repositories: Repository[] = [];
}
