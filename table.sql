CREATE DATABASE SmartParkingSystem;

USE SmartParkingSystem;

CREATE TABLE Users(
    User_ID INT PRIMARY KEY IDENTITY(1,1),
    Name VARCHAR(255) NOT NULL,
    Contact VARCHAR(20),
    Email VARCHAR(255) UNIQUE,
    Password VARCHAR(255) NOT NULL -- Added from LOGINS entity
);

CREATE TABLE Vehicles (
    Vehicle_ID INT PRIMARY KEY IDENTITY(1,1),
    Vehicle_No VARCHAR(20) UNIQUE NOT NULL,
    Type VARCHAR(50),
    User_ID INT,
    FOREIGN KEY (User_ID) REFERENCES Users(User_ID)
);

CREATE TABLE Parking_Lots (
    Lot_ID INT PRIMARY KEY IDENTITY(1,1),
    Location VARCHAR(255) NOT NULL,
    Capacity INT,
    Available_Spaces INT
);

CREATE TABLE Parking_Slots (
    Slot_ID INT PRIMARY KEY IDENTITY(1,1),
    Slot_Type VARCHAR(50), -- Assuming 'Slot_Type' corresponds to the 'Type' in PARKING SLOTS entity
    Status VARCHAR(50),
    Lot_ID INT,
    FOREIGN KEY (Lot_ID) REFERENCES Parking_Lots(Lot_ID)
);

CREATE TABLE Bookings (
    Booking_ID INT PRIMARY KEY IDENTITY(1,1),
    Start_Time DATETIME,
    End_Time DATETIME,
    Status VARCHAR(50),
    User_ID INT,
    Slot_ID INT,
    Vehicle_ID INT, -- Added to link booking to a specific vehicle
    FOREIGN KEY (User_ID) REFERENCES Users(User_ID),
    FOREIGN KEY (Slot_ID) REFERENCES Parking_Slots(Slot_ID),
    FOREIGN KEY (Vehicle_ID) REFERENCES Vehicles(Vehicle_ID) -- Foreign key for Vehicle
);

CREATE TABLE Payments (
    Payment_ID INT PRIMARY KEY IDENTITY(1,1),
    Booking_ID INT,
    Amount DECIMAL(10, 2),
    Method VARCHAR(50),
    Status VARCHAR(50), -- Added Payment Status
    Timestamp DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (Booking_ID) REFERENCES Bookings(Booking_ID)
);

CREATE TABLE Admins(
    Admin_ID INT PRIMARY KEY IDENTITY(1,1),
    Password VARCHAR(255) NOT NULL,
    Date_of_Joining DATE,
    Shift_Timing VARCHAR(100)
);



/*Decrease available spaces after booking*/

CREATE TRIGGER UpdateAvailableSpacesAfterBooking
ON Bookings
AFTER INSERT, UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (SELECT 1 FROM inserted WHERE Status = 'Booked'
                AND (NOT EXISTS (SELECT 1 FROM deleted WHERE deleted.Booking_ID = inserted.Booking_ID AND deleted.Status = 'Booked') OR NOT EXISTS (SELECT 1 FROM deleted)))
    BEGIN
        UPDATE Parking_Lots
        SET Available_Spaces = Available_Spaces - 1
        WHERE Lot_ID IN (SELECT ps.Lot_ID FROM Parking_Slots ps JOIN inserted i ON ps.Slot_ID = i.Slot_ID);

        IF @@ROWCOUNT = 0
        BEGIN
            RAISERROR('Error updating available spaces in Parking Lots.', 16, 1)
            ROLLBACK TRANSACTION
            RETURN
        END
    END
END;



/*Stored Procedure Add new booking*/

CREATE PROCEDURE AddNewBooking
    @UserID INT,
    @LotID INT,
    @VehicleID INT,
    @StartTime DATETIME,
    @EndTime DATETIME
AS
BEGIN
    SET NOCOUNT ON;

    -- Declare a variable to hold the available Slot ID
    DECLARE @AvailableSlotID INT;

    -- Find the first available slot in the specified lot
    SELECT TOP 1 @AvailableSlotID = ps.Slot_ID
    FROM Parking_Slots ps
    WHERE ps.Lot_ID = @LotID AND ps.Status = 'Available';

    -- Check if an available slot was found
    IF @AvailableSlotID IS NULL
    BEGIN
        RAISERROR('No available parking slots in the selected lot.', 16, 1);
        RETURN;
    END;

    -- Insert the new booking
    INSERT INTO Bookings (User_ID, Slot_ID, Vehicle_ID, Start_Time, End_Time, Status)
    VALUES (@UserID, @AvailableSlotID, @VehicleID, @StartTime, @EndTime, 'Booked');

    -- Update the status of the booked parking slot to 'Occupied'
    UPDATE Parking_Slots
    SET Status = 'Occupied'
    WHERE Slot_ID = @AvailableSlotID;

    -- Optionally, create a pending payment record
    INSERT INTO Payments (Booking_ID, Amount, Method, Status)
    VALUES (SCOPE_IDENTITY(), 0.00, 'Pending', 'Pending');

    -- Return the newly created Booking ID
    SELECT 'Booking successful. Booking ID: ' + CAST(SCOPE_IDENTITY() AS VARCHAR);

END;
GO

/*get available slots by lot*/

CREATE PROCEDURE GetAvailableSlotsByLot (
    @LotID INT
)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT Slot_ID, Slot_Type
    FROM Parking_Slots
    WHERE Lot_ID = @LotID AND Status = 'Available';
END;

/*Record payment*/

CREATE PROCEDURE RecordPayment (
    @BookingID INT,
    @Amount DECIMAL(10, 2),
    @Method VARCHAR(50)
)
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE Payments
    SET Amount = @Amount,
        Method = @Method,
        Status = 'Paid',
        Timestamp = GETDATE()
    WHERE Booking_ID = @BookingID AND Status <> 'Paid';

    IF @@ROWCOUNT = 0
    BEGIN
        RAISERROR('Payment record not updated. Either the booking does not exist or payment is already recorded.', 16, 1)
        RETURN
    END

    SELECT 'Payment recorded successfully for Booking ID: ' + CAST(@BookingID AS VARCHAR);
END;


select * from Users
select * from Vehicles
select * from Parking_Lots
select * from Parking_Slots
select * from Bookings
select * from Payments
select * from Admins

DELETE FROM Users;
DELETE FROM Vehicles;
DELETE FROM Parking_Lots;
DELETE FROM Parking_Slots;
DELETE FROM Bookings;
DELETE FROM Payments;

drop database SmartParkingSystem