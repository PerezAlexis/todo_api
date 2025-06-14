const API_BASE = 'http://localhost/todo_api/api.php/tareas';

// Carga y muestra todas las tareas
async function loadTasks() {
  const res = await fetch(API_BASE);
  const tasks = await res.json();
  const ul = document.getElementById('tasks');
  ul.innerHTML = '';
  tasks.forEach(t => {
    const li = document.createElement('li');
    li.className = t.completada ? 'completed' : '';
    li.innerHTML = `
      <span>${t.titulo}</span>
      <div>
        <button onclick="toggleComplete(${t.id}, ${t.completada})">
          ${t.completada ? '↺' : '✔'}
        </button>
        <button onclick="deleteTask(${t.id})">✖</button>
      </div>
    `;
    ul.appendChild(li);
  });
}

// Crear nueva tarea
document.getElementById('add-btn').addEventListener('click', async () => {
  const input = document.getElementById('task-input');
  const title = input.value.trim();
  if (!title) return alert('Escribe algo primero');
  await fetch(API_BASE, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ titulo: title })
  });
  input.value = '';
  loadTasks();
});

// Alternar estado completada (PUT)
async function toggleComplete(id, current) {
  // Primero obtenemos título actual para no perderlo
  const resp = await fetch(`${API_BASE}/${id}`),
        tarea = await resp.json();
  await fetch(`${API_BASE}/${id}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      titulo: tarea.titulo,
      completada: current ? 0 : 1
    })
  });
  loadTasks();
}

// Borrar tarea (DELETE)
async function deleteTask(id) {
  if (confirm('¿Borrar esta tarea?')) {
    await fetch(`${API_BASE}/${id}`, { method: 'DELETE' });
    loadTasks();
  }
}

// Inicia al cargar la página
window.addEventListener('DOMContentLoaded', loadTasks);
