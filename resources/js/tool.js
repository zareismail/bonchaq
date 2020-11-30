Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'bonchaq',
      path: '/bonchaq',
      component: require('./components/Tool'),
    },
  ])
})
