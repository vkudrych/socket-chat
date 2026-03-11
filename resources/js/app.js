import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {

    const container = document.getElementById('messages')
    let currentReceiverId = null
    let echoChannel = null

    const scrollBtn = document.createElement('button')
    scrollBtn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>`
    scrollBtn.style.cssText = `
        position: absolute; bottom: 90px; right: calc(50% - 18px);
        width: 36px; height: 36px; border-radius: 50%;
        background: #5b6ef5; color: #fff; border: none;
        cursor: pointer; display: none; align-items: center;
        justify-content: center; box-shadow: 0 2px 8px rgba(91,110,245,0.4); z-index: 10;
    `
    document.querySelector('.chat-area').style.position = 'relative'
    document.querySelector('.chat-area').appendChild(scrollBtn)

    scrollBtn.addEventListener('click', () => {
        container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' })
    })

    container.addEventListener('scroll', () => {
        const isNearBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 100
        scrollBtn.style.display = isNearBottom ? 'none' : 'flex'
    })

    function subscribeToChannel() {
        if (echoChannel) Echo.leave(`user.${window.userId}`)

        echoChannel = Echo.private(`user.${window.userId}`)
            .listen('.message.sent', (e) => {
                const senderId = String(e.message.sender_id)
                if (senderId === String(currentReceiverId)) {
                    appendMessage(e.message.message, 'incoming', e.message.created_at, e.message.id, false)
                } else {
                    const badge = document.getElementById(`badge-${senderId}`)
                    if (badge) {
                        badge.textContent = (parseInt(badge.textContent) || 0) + 1
                        badge.classList.remove('hidden')
                    }
                }
            })
            .listen('.messages.read', (e) => {
                if (String(e.readerId) === String(currentReceiverId)) {
                    document.querySelectorAll('.message.outgoing .checks').forEach(el => {
                        el.classList.add('read')
                    })
                }
            })
    }

    async function openChat(userId, userName) {
        currentReceiverId = userId
        document.getElementById('receiver_id').value = userId
        document.getElementById('chat-with').textContent = userName

        document.querySelectorAll('.user-item').forEach(i => i.classList.remove('active'))
        document.querySelector(`[data-user-id="${userId}"]`)?.classList.add('active')

        const badge = document.getElementById(`badge-${userId}`)
        if (badge) {
            badge.textContent = ''
            badge.classList.add('hidden')
        }

        container.style.visibility = 'hidden'
        container.innerHTML = ''

        const res = await fetch(`/conversation/${userId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        const messages = await res.json()

        messages.forEach(msg => {
            appendMessage(
                msg.message,
                String(msg.sender_id) === String(window.userId) ? 'outgoing' : 'incoming',
                msg.created_at,
                msg.id,
                !!msg.read_at
            )
        })

        container.style.scrollBehavior = 'auto'
        container.scrollTop = container.scrollHeight
        container.style.scrollBehavior = ''

        container.style.visibility = 'visible'
    }

    document.querySelectorAll('.user-item').forEach(item => {
        item.addEventListener('click', () => {
            openChat(item.dataset.userId, item.dataset.userName)
        })
    })

    const firstItem = document.querySelector('.user-item')
    if (firstItem) {
        openChat(firstItem.dataset.userId, firstItem.dataset.userName)
    }

    const form = document.getElementById('main-form')
    if (form) {
        form.onsubmit = async (e) => {
            e.preventDefault()
            e.stopImmediatePropagation()

            const input = document.getElementById('message-input')
            const message = input.value.trim()
            if (!message || !currentReceiverId) return

            appendMessage(message, 'outgoing')
            input.value = ''

            try {
                await fetch('/messages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ receiver_id: currentReceiverId, message })
                })
            } catch (err) {
                console.error('Failed to send message', err)
            }
        }
    }

    function appendMessage(text, type, time = null, msgId = null, isRead = false) {
        const div = document.createElement('div')
        div.className = `message ${type}`
        if (msgId) div.dataset.id = msgId

        const timeStr = time
            ? new Date(time).toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit' })
            : new Date().toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit' })

        const checks = type === 'outgoing' ? `
            <span class="checks ${isRead ? 'read' : ''}">
                <svg width="16" height="11" viewBox="0 0 16 11" fill="none">
                    <path d="M1 5.5L4.5 9L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path class="second-check" d="M5 5.5L8.5 9L15 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>` : ''

        div.innerHTML = `
            <div class="bubble">
                ${text}
                <div class="meta">
                    <span class="time">${timeStr}</span>
                    ${checks}
                </div>
            </div>
        `
        container.appendChild(div)

        const isNearBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 150
        if (isNearBottom) {
            container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' })
        } else {
            scrollBtn.style.display = 'flex'
        }
    }

    subscribeToChannel()
})