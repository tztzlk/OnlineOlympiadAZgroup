import api from './api'

const textEncoder = new TextEncoder()

function toHex(buffer) {
  return Array.from(new Uint8Array(buffer))
    .map((byte) => byte.toString(16).padStart(2, '0'))
    .join('')
}

async function sha256(value) {
  const digest = await crypto.subtle.digest('SHA-256', textEncoder.encode(value))
  return toHex(digest)
}

export async function solveProofOfWork(context) {
  const { data } = await api.get('/security/pow-challenge', { params: { context } })
  const prefix = '0'.repeat(data.difficulty || 3)
  let nonce = 0

  while (true) {
    const candidate = String(nonce)
    const hash = await sha256(`${data.token}|${candidate}`)

    if (hash.startsWith(prefix)) {
      return {
        pow_token: data.token,
        pow_nonce: candidate,
      }
    }

    nonce += 1
  }
}
