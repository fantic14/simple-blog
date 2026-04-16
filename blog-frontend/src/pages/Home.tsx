import { useEffect, useState } from "react";
import api from "../api/axios";

interface User {
    id: number;
    name: string;
    email: string;
}

interface Post {
    id: number;
    title: string;
    content: string;
    user_id: number;
    created_at: string;
    updated_at: string;
    user?: User;
}

interface Comment {
    id: number;
    comment: string;
    user_id: number;
    post_id: number;
    created_at: string;
    user?: User;
}

export default function Home() {
    const [posts, setPosts] = useState<Post[]>([]);
    const [loading, setLoading] = useState(true);
    const [selectedPost, setSelectedPost] = useState<Post | null>(null);
    const [comments, setComments] = useState<Comment[]>([]);
    const [newComment, setNewComment] = useState("");
    const [submittingComment, setSubmittingComment] = useState(false);

    // Create Post States
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [newPostTitle, setNewPostTitle] = useState("");
    const [newPostContent, setNewPostContent] = useState("");
    const [submittingPost, setSubmittingPost] = useState(false);
    const isLoggedIn = !!localStorage.getItem('auth_token');

    const fetchPosts = async () => {
        try {
            const response = await api.get('/posts');
            if (response.data && Array.isArray(response.data.data)) {
                setPosts(response.data.data);
            } else if (Array.isArray(response.data)) {
                setPosts(response.data);
            }
        } catch (error) {
            console.error('Error fetching posts:', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchPosts();
    }, []);

    // Fetch comments when a post is selected
    useEffect(() => {
        if (selectedPost) {
            const fetchComments = async () => {
                try {
                    const response = await api.get(`/posts/${selectedPost.id}/comments`);
                    if (response.data && Array.isArray(response.data.data)) {
                        setComments(response.data.data);
                    }
                } catch (error) {
                    console.error('Error fetching comments:', error);
                }
            };
            fetchComments();
        } else {
            setComments([]);
        }
    }, [selectedPost]);

    useEffect(() => {
        if (selectedPost || isCreateModalOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'unset';
        }
        return () => {
            document.body.style.overflow = 'unset';
        };
    }, [selectedPost, isCreateModalOpen]);

    const handleCommentSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedPost || !newComment.trim()) return;

        setSubmittingComment(true);
        try {
            const response = await api.post(`/posts/${selectedPost.id}/comments`, {
                comment: newComment
            });
            if (response.data && response.data.success) {
                setComments([response.data.data, ...comments]);
                setNewComment("");
            }
        } catch (error) {
            console.error('Error posting comment:', error);
        } finally {
            setSubmittingComment(false);
        }
    };

    const handlePostSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newPostTitle.trim() || !newPostContent.trim()) return;

        setSubmittingPost(true);
        try {
            const response = await api.post('/posts', {
                title: newPostTitle,
                content: newPostContent
            });
            if (response.data && response.data.success) {
                setNewPostTitle("");
                setNewPostContent("");
                setIsCreateModalOpen(false);
                fetchPosts();
            }
        } catch (error) {
            console.error('Error creating post:', error);
            alert("Failed to create post. Please check your login status.");
        } finally {
            setSubmittingPost(false);
        }
    };

    if (loading) {
        return (
            <div className="flex flex-col justify-center items-center h-96">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-600 mb-4"></div>
                <p className="text-gray-500 font-medium">Loading stories...</p>
            </div>
        );
    }

    return (
        <div className="space-y-12">
            <header className="relative py-16 px-8 text-center bg-indigo-900 rounded-3xl overflow-hidden shadow-2xl">
                <div className="absolute inset-0 bg-gradient-to-br from-blue-600/40 to-purple-700/40 backdrop-blur-sm"></div>
                <div className="relative z-10">
                    <h1 className="text-4xl md:text-6xl font-black mb-6 text-white tracking-tight">
                        The Digital <span className="text-blue-300">Journal</span>
                    </h1>
                    <p className="text-blue-100 text-xl max-w-2xl mx-auto leading-relaxed font-light">
                        Explore thoughts, insights, and stories from our vibrant community of writers.
                    </p>
                </div>
            </header>

            {/* Floating Action Button for Creating Posts */}
            {isLoggedIn && (
                <button
                    onClick={() => setIsCreateModalOpen(true)}
                    className="fixed bottom-10 right-10 z-[80] w-16 h-16 bg-blue-600 text-white rounded-2xl shadow-2xl shadow-blue-400/50 flex items-center justify-center hover:bg-blue-700 hover:scale-110 active:scale-95 transition-all duration-300 group"
                    title="Create Post"
                >
                    <svg className="w-8 h-8 group-hover:rotate-90 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {posts.length > 0 ? (
                    posts.map((post) => (
                        <article
                            key={post.id}
                            className="flex flex-col bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden group cursor-pointer"
                            onClick={() => setSelectedPost(post)}
                        >
                            <div className="p-8 flex flex-col flex-grow">
                                <h2 className="text-2xl font-bold mb-4 text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2">
                                    {post.title}
                                </h2>
                                <p className="text-gray-600 mb-8 flex-grow leading-relaxed line-clamp-3">
                                    {post.content}
                                </p>
                                <div className="flex items-center justify-between mt-auto pt-6 border-t border-gray-50">
                                    <div className="flex items-center space-x-2">
                                        <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-black text-[10px]">
                                            {post.user?.name ? post.user.name[0].toUpperCase() : 'U'}
                                        </div>
                                        <span className="text-sm font-medium text-gray-500">
                                            {new Date(post.created_at).toLocaleDateString(undefined, { month: 'long', day: 'numeric' })}
                                        </span>
                                    </div>
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setSelectedPost(post);
                                        }}
                                        className="text-blue-600 font-bold text-sm hover:text-blue-800 transition-colors uppercase tracking-widest cursor-pointer"
                                    >
                                        Read More
                                    </button>
                                </div>
                            </div>
                        </article>
                    ))
                ) : (
                    <div className="col-span-full py-24 text-center bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                        <div className="text-6xl mb-6">🖋️</div>
                        <h3 className="text-2xl font-bold text-gray-800 mb-2">Silence in the library</h3>
                        <p className="text-gray-500 max-w-md mx-auto">It seems no one has written anything yet. Why don't you be the first to share your thoughts?</p>
                        <button
                            onClick={() => isLoggedIn ? setIsCreateModalOpen(true) : window.location.href = '/login'}
                            className="mt-8 bg-blue-600 text-white px-8 py-3 rounded-full font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200"
                        >
                            Create First Post
                        </button>
                    </div>
                )}
            </div>

            {selectedPost && (
                <div
                    className="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-8 animate-in fade-in duration-300"
                    onClick={() => setSelectedPost(null)}
                >
                    <div className="absolute inset-0 bg-gray-900/60 backdrop-blur-md transition-opacity"></div>

                    <div
                        className="relative bg-white w-full max-w-4xl max-h-[85vh] overflow-y-auto rounded-[2.5rem] shadow-2xl transition-all animate-in zoom-in-95 duration-300"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <button
                            onClick={() => setSelectedPost(null)}
                            className="absolute top-8 right-8 p-3 rounded-2xl bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-900 transition-all z-10"
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <div className="p-8 md:p-16">
                            <div className="flex flex-wrap items-center gap-4 mb-8">
                                <span className="text-gray-400 text-sm font-semibold flex items-center">
                                    <span className="w-1.5 h-1.5 rounded-full bg-gray-300 mr-2"></span>
                                    {new Date(selectedPost.created_at).toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' })}
                                </span>
                            </div>

                            <h2 className="text-4xl md:text-6xl font-black text-gray-900 mb-10 leading-[1.1] tracking-tight">
                                {selectedPost.title}
                            </h2>

                            <div className="flex items-center space-x-5 mb-12 py-8 border-y border-gray-50">
                                <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-black text-2xl shadow-xl shadow-blue-100">
                                    {selectedPost.user?.name ? selectedPost.user.name[0].toUpperCase() : 'U'}
                                </div>
                                <div className="flex flex-col">
                                    <p className="text-gray-900 font-extrabold text-lg leading-tight">{selectedPost.user?.name || "Anonymous Author"}</p>
                                    <p className="text-gray-400 text-sm font-bold uppercase tracking-widest">{selectedPost.user?.email || "Sharing their wisdom"}</p>
                                </div>
                            </div>

                            <div className="prose prose-xl max-w-none border-b border-gray-100 pb-12 mb-12">
                                <p className="text-gray-700 leading-relaxed whitespace-pre-wrap text-[1.35rem] font-medium selection:bg-blue-100">
                                    {selectedPost.content}
                                </p>
                            </div>

                            <section className="space-y-8">
                                <h3 className="text-2xl font-black text-gray-900 flex items-center mb-6">
                                    Comments
                                    <span className="ml-3 bg-blue-100 text-blue-600 text-sm font-black px-3 py-0.5 rounded-full">
                                        {comments.length}
                                    </span>
                                </h3>

                                <form onSubmit={handleCommentSubmit} className="mb-12">
                                    <div className="relative group">
                                        <textarea
                                            value={newComment}
                                            onChange={(e) => setNewComment(e.target.value)}
                                            placeholder="Join the conversation..."
                                            className="w-full bg-gray-50 border-2 border-gray-100 rounded-2xl p-6 min-h-[120px] focus:bg-white focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all duration-300 text-gray-700 font-medium resize-none shadow-inner"
                                        />
                                        <button
                                            type="submit"
                                            disabled={submittingComment || !newComment.trim()}
                                            className="absolute bottom-4 right-4 bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-blue-200 active:scale-95"
                                        >
                                            {submittingComment ? "Posting..." : "Post Comment"}
                                        </button>
                                    </div>
                                </form>

                                <div className="space-y-6">
                                    {comments.length > 0 ? (
                                        comments.map((comment) => (
                                            <div key={comment.id} className="flex space-x-4 animate-in fade-in duration-300">
                                                <div className="flex-shrink-0 w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 font-bold text-sm">
                                                    {(comment.user?.name?.[0] || 'U').toUpperCase()}
                                                </div>
                                                <div className="flex-grow bg-[#f8fafc] rounded-2xl p-5 border border-gray-50">
                                                    <div className="flex items-center justify-between mb-2">
                                                        <span className="font-black text-gray-900 text-sm">
                                                            {comment.user?.name || `User ${comment.user_id}`}
                                                        </span>
                                                        <span className="text-xs font-bold text-gray-400">
                                                            {new Date(comment.created_at).toLocaleDateString()}
                                                        </span>
                                                    </div>
                                                    <p className="text-gray-600 leading-relaxed font-medium">
                                                        {comment.comment}
                                                    </p>
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="text-center py-10 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-100">
                                            <p className="text-gray-400 font-bold italic">No comments yet. Start the conversation!</p>
                                        </div>
                                    )}
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            )}
            {/* Create Post Modal */}
            {isCreateModalOpen && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-8 animate-in fade-in duration-300">
                    <div className="absolute inset-0 bg-gray-900/60 backdrop-blur-md px-4" onClick={() => setIsCreateModalOpen(false)}></div>
                    <div className="relative bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
                        <div className="p-8 md:p-12">
                            <div className="flex justify-between items-center mb-10">
                                <div>
                                    <h2 className="text-3xl font-black text-gray-900 tracking-tight">New Story</h2>
                                    <p className="text-gray-500 font-medium mt-1">Share your thoughts with the world</p>
                                </div>
                                <button
                                    onClick={() => setIsCreateModalOpen(false)}
                                    className="p-3 rounded-2xl bg-gray-50 text-gray-400 hover:text-gray-900 transition-colors"
                                >
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <form onSubmit={handlePostSubmit} className="space-y-6">
                                <div>
                                    <label className="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3 ml-1">Title</label>
                                    <input
                                        type="text"
                                        required
                                        value={newPostTitle}
                                        onChange={(e) => setNewPostTitle(e.target.value)}
                                        placeholder="Story title..."
                                        className="w-full bg-gray-50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:bg-white focus:border-blue-600 outline-none transition-all duration-300 text-gray-900 font-bold placeholder:text-gray-300 text-xl"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3 ml-1">Content</label>
                                    <textarea
                                        required
                                        value={newPostContent}
                                        onChange={(e) => setNewPostContent(e.target.value)}
                                        placeholder="Tell your story..."
                                        className="w-full bg-gray-50 border-2 border-gray-100 rounded-2xl px-6 py-4 min-h-[200px] focus:bg-white focus:border-blue-600 outline-none transition-all duration-300 text-gray-700 font-medium leading-relaxed resize-none"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    disabled={submittingPost}
                                    className="w-full py-5 bg-blue-600 text-white rounded-2xl font-black text-xl hover:bg-blue-700 shadow-xl shadow-blue-200 transition-all active:scale-[0.98] disabled:opacity-50 flex items-center justify-center space-x-3"
                                >
                                    {submittingPost ? "Publishing..." : "Publish Story"}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}