import { Routes, Route, Link, useLocation } from 'react-router-dom';
import Home from './pages/Home';
import Login from './pages/Login';
import Register from './pages/Register';

function App() {
  const location = useLocation();
  const isLoggedIn = !!localStorage.getItem('auth_token');

  const handleLogout = () => {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    window.location.reload();
  };

  return (
    <div className="min-h-screen bg-[#f8fafc] text-gray-900 font-sans selection:bg-blue-100 selection:text-blue-900">

      <nav className="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm">
        <div className="max-w-6xl mx-auto px-6 h-20 flex justify-between items-center">
          <Link to="/" className="group flex items-center space-x-2">
            <div className="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-200 group-hover:scale-110 transition-transform duration-300">
              <span className="text-xl font-black">B</span>
            </div>
            <span className="text-2xl font-black tracking-tight text-gray-900 group-hover:text-blue-600 transition-colors">
              Simple Blog<span className="text-blue-600">.</span>
            </span>
          </Link>

          <div className="hidden md:flex items-center space-x-8">
            <Link
              to="/"
              className={`text-sm font-bold uppercase tracking-widest transition-colors ${location.pathname === '/' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900'}`}
            >
              Feed
            </Link>
            <div className="h-4 w-px bg-gray-200"></div>

            {!isLoggedIn ? (
              <>
                <Link
                  to="/login"
                  className={`text-sm font-bold uppercase tracking-widest transition-colors ${location.pathname === '/login' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900'}`}
                >
                  Sign In
                </Link>
                <Link
                  to="/register"
                  className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-full text-sm font-bold uppercase tracking-widest shadow-lg shadow-blue-100 hover:shadow-blue-200 transition-all active:scale-95"
                >
                  Get Started
                </Link>
              </>
            ) : (
              <button
                onClick={handleLogout}
                className="text-sm font-black uppercase tracking-widest text-red-500 hover:text-red-700 transition-colors cursor-pointer"
              >
                Log Out
              </button>
            )}
          </div>
        </div>
      </nav>

      <main className="max-w-6xl mx-auto px-6 py-12">
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
        </Routes>
      </main>

      <footer className="mt-20 py-12 border-t border-gray-100 bg-white shadow-inner">
        <div className="max-w-6xl mx-auto px-6 text-center">
          <p className="text-gray-400 text-sm font-medium">© 2026 Simple Blog.</p>
        </div>
      </footer>

    </div>
  );
}

export default App;