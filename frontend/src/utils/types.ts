type HTMLElementEvent<T extends HTMLElement> = Event & {
  target: T
}

export type { HTMLElementEvent }
