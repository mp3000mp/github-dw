<script lang="ts" setup>
import { ref } from 'vue'

const isEmailShown = ref(false)
const email = ref('my email')

function animateEmail() {
  isEmailShown.value = true
  email.value = 'moussadedijon@gmail.com'
  // setTimeout(() => {
  //   email.value = 'moussadedijon@gmail.com'
  // }, 400)
}
</script>

<template>
  <div class="container">
    <div class="row">
      <div class="p-3 app-block col-md-9 mx-auto mb-2">
        <h2>About</h2>
        <h3>Where does the data come from ?</h3>
        <p class="text-justify">
          The database has been made using the
          <a href="https://docs.github.com/en/rest" title="github api doc">Github public API</a>. I
          respect the Github rate limiter policy and I don't want to flood their service. That means
          not all Github projects are presents here and it's fine like that. Please consider this
          project as a POC, doing this seriously would need a partnership with Github.
        </p>
        <h3>What can I find here ?</h3>
        <ul>
          <li>
            PHP projects containing a
            <a href="https://getcomposer.org/doc/04-schema.md">composer.json</a> file in the root
            folder.
          </li>
          <li>
            Javascript projects containing a
            <a href="https://docs.npmjs.com/cli/v8/configuring-npm/package-json">package.json</a>
            file in the root folder.
          </li>
          <li>
            Go projects containing a <a href="https://go.dev/ref/mod#go-mod-file">go.mod</a> file in
            the root folder.
          </li>
          <li>
            Python projects containing a
            <a href="https://docs.python.org/3/tutorial/venv.html">requirements.txt</a> file in the
            root folder.
          </li>
        </ul>
        <h3>What can't I find here ?</h3>
        <ul>
          <li>All projects that does not fulfil the previous requirements.</li>
          <li>Projects that are too small in size (considered uninteresting).</li>
          <li>Projects that have not been updated recently.</li>
        </ul>
        <h3>Why not more languages ?</h3>
        <p>Because I'm lazy.</p>
        <h3>Is this project open source ?</h3>
        <p>
          Not yet because it's dirty. But maybe one day and you'll be able to find it by looking for
          <a href="https://symfony.com/doc/current/index.html" title="awesome backend framework"
            >Symfony</a
          >
          and
          <a href="https://vuejs.org/guide/introduction.html" title="awesome frontend framework"
            >Vue.js</a
          >
          dependencies.
        </p>
        <h3>I have more questions, what can I do ?</h3>
        <p>
          Please don't panic and contact me at
          <span class="btn cp" @click="animateEmail" :class="isEmailShown ? ['hidden'] : ['shown']"
            >my email</span
          ><a
            href="mailto:moussadedijon@gmail.com"
            title="Please no spam"
            :class="!isEmailShown ? ['hidden'] : ['shown']"
            >{{ email }}</a
          >.
        </p>
        <div class="text-end">
          <router-link :to="{ name: 'home' }">Go back to form</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
h2 {
  font-size: 160%;
  text-align: center;
}

h3 {
  margin-top: 20px;
}

.shown {
  display: inline;
}

.hidden {
  display: none;
}
</style>
