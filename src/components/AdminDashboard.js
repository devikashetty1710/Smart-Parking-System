import React from 'react';
import { getCurrentUser } from '../services/authService';

const AdminDashboard = () => {
    const user = getCurrentUser();

    return (
        <div className="container mt-4">
            <div className="card">
                <div className="card-header">
                    <h2>Admin Dashboard</h2>
                </div>
                <div className="card-body">
                    <h5 className="card-title">Welcome, {user?.fullName}</h5>
                    <p className="card-text">This is the admin dashboard. Only administrators can access this page.</p>
                    
                    <div className="row mt-4">
                        <div className="col-md-4">
                            <div className="card">
                                <div className="card-body">
                                    <h5 className="card-title">User Management</h5>
                                    <p className="card-text">Manage system users</p>
                                    <button className="btn btn-primary">Manage Users</button>
                                </div>
                            </div>
                        </div>
                        
                        <div className="col-md-4">
                            <div className="card">
                                <div className="card-body">
                                    <h5 className="card-title">Parking Management</h5>
                                    <p className="card-text">Manage parking spaces</p>
                                    <button className="btn btn-primary">Manage Parking</button>
                                </div>
                            </div>
                        </div>
                        
                        <div className="col-md-4">
                            <div className="card">
                                <div className="card-body">
                                    <h5 className="card-title">Reports</h5>
                                    <p className="card-text">View system reports</p>
                                    <button className="btn btn-primary">View Reports</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AdminDashboard; 