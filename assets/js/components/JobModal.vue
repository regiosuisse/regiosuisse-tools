<template>
    <div class="job-modal-overlay" @click.self="close">
      <div class="job-modal-content">
        <button class="job-modal-close" @click="close">×</button>
        <slot></slot>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    methods: {
      close() {
        this.$emit('close');
      },
    },
  };
  </script>
  
  <style scoped>
  .job-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
  }
  
  .job-modal-content {
    padding: 20px;
    border-radius: 5px;
    width: 80%;
    position: relative;
  }
  
  .job-modal-close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
    color: #fff;
  }
  </style>