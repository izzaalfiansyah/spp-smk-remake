const apiURL = window.apiURL || 'http://localhost:8000';
const apiHeader = {
	'Content-Type': 'application/json',
};

export const http = {
	get: (url, params) => {
		const query = params ? '?' + new URLSearchParams(params).toString() : '';
		return fetch(apiURL + url + query, {
			method: 'GET',
			headers: apiHeader,
		});
	},
	post: (url, params) => {
		return fetch(apiURL + url, {
			method: 'POST',
			headers: apiHeader,
			body: JSON.stringify(params),
		});
	},
	put: (url, params) => {
		return fetch(apiURL + url, {
			method: 'PUT',
			headers: apiHeader,
			body: JSON.stringify(params),
		});
	},
	delete: (url) => {
		return fetch(apiURL + url, {
			method: 'DELETE',
			headers: apiHeader,
		});
	},
};

export const notify = (message, color = 'bg-green-400') => {
	const notification = document.createElement('div');
	notification.innerHTML = `
	<div class="fixed p-4 z-30 bottom-5 left-0 right-0 flex justify-center pointer-events-none">
		<div class="p-4 lg:min-w-500px min-w-full animate-up pointer-events-auto text-white max-w-full rounded shadow-lg ${color}">${message}</div>
	</div>
	`;

	document.body.appendChild(notification);

	setTimeout(() => notification.remove(), 2000);
};

export const formatMoney = (value) => {
	const number = parseInt(value);
	return 'Rp ' + number.toLocaleString('id-ID');
};

export const auth = {
	id: localStorage.getItem('id'),
	role: localStorage.getItem('role'),
};

export const formatDate = (value) => {
	const date = new Date(value);

	let dateID = date.getDate();
	dateID += ' ';
	dateID += [
		'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember',
	][date.getMonth()];
	dateID += ' ';
	dateID += date.getFullYear();

	return dateID;
};

export const blankPage = (url) => {
	const a = document.createElement('a');
	a.target = '_blank';
	a.href = url;

	new Promise((resolve) => {
		a.click();
		resolve(true);
	}).then(() => {
		a.remove();
	});
};

export const nowDate = () => {
	const date = new Date();
	let dates = date.getFullYear();
	dates += '-';
	dates += date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1;
	dates += '-';
	dates += date.getDate() < 10 ? '0' + date.getDate() : date.getDate();

	return dates;
};

export const readFile = (file) => {
	return new Promise((resolve, reject) => {
		const reader = new FileReader();
		reader.readAsDataURL(file);

		try {
			reader.onload = () => {
				resolve(reader.result);
			};
		} catch (err) {
			reject(err);
		}
	});
};
