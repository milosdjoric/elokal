export default defineNuxtPlugin(async () => {
  const { load } = useShippingConfig()
  await load()
})
