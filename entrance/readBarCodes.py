# -------------------------------------------------------------------------------------------------------
#                                                                                                       |
#                                      SCRIPT FOR READING THE BARCODES                                  |
#                                                                                                       |
# -------------------------------------------------------------------------------------------------------

import os
import mysql.connector
from mysql.connector import Error,RefreshOption
from dotenv import load_dotenv, dotenv_values
load_dotenv(dotenv_path='./affiliations/php/.env')

from datetime import datetime
import time

# Define base variables
notEveryOneIsCheckedIn = True
firstCheckIn = True
dateForMorMoney = 1744396200 # 11.04.2025 um 20:00:00 Uhr GMT
#dateForMorMoney = time.time()
#dateForMorMoney = 1743022456 # force xtraMoney

# Variables for database
zuschlag_price = 2.50
zuschlag_bol = False

# Verbindung zur Datenbank herstellen
def connect_to_database():
    try:
        cnx = mysql.connector.connect(
            host=os.getenv("DB_HOST"),
            user=os.getenv("DB_USERNAME"),
            password=os.getenv("DB_PASSWORD"),
            database=os.getenv("DB_NAME")
        )
        return cnx
    except Error as e:
        print(f"Fehler beim Verbinden mit der Datenbank: {e}")
        return None

# Prüfen, ob Einlass überhaupt offen ist
def isEntranceOpen():
    cursor.execute("SELECT status FROM controlls WHERE service = 'Einlass'")
    result = cursor.fetchone()
    if result[0] == 0:
        return False
    else: 
        return True

def checkIn(id):
    global zuschlag_bol

    print("⌛ Reqeusting check-in for ID-Nr. '" + id + "'\n⌛ Working on your Reqeust...")

    # 0. Check if this id exists
    cursor.execute("SELECT * FROM tickets WHERE id = %s", (id,))
    if cursor.fetchone() is None:
        print("❌ ID-Nr. '" + id + "' does not exist in the database")
        return
    else:
        print("✅ ID-Nr. '" + id + "' exists in the database")
    
    # 0.1 Check if ticket is already checked in
    cursor.execute("SELECT Bar_einlass FROM tickets WHERE id = %s",(id,))
    row = cursor.fetchone()
    if row[0]:
        print("❌ ID-Nr. '" + id + "' is already checked in")
        return
    else:
        print("✅ ID-Nr. '" + id + "' is not checked in")
    
    # 1. Set the timestamp
    now_uf = datetime.now()
    now_f = now_uf.strftime("%Y/%m/%d - %H:%M:%S")
    print("✅ Seted timestamp: " + now_f)

    # 2. Check for Extra Money
    current_timestamp = time.time() # current UNIX-Timestamp
    if current_timestamp >= dateForMorMoney: # Are we before or behin this Timestamp?
        print("⌛ Looking for the current time and evaluating for some extra Money... - 🎉 Money Money Money... must be funny!") # we are
        zuschlag_bol = True
        cursor.execute("UPDATE tickets SET zuschlag = %s WHERE id = %s",(zuschlag_price,id,))
    else:
        print("⌛ Looking for the current time and evaluating for some extra Money... - ❌ Das ist eine Traurigkeit. Noch kein Extra Geld") # we are NOT

    # 3. Update the Database and trigger the Event-Listener
    try:
        cursor.execute("UPDATE tickets SET Bar_Einlass = 1, ts_einlass = %s WHERE ID = %s",(now_f, id))
        cnx.commit()
        print("✅ Check-In succesfull / Ticket updated successfully")

    except Error as e:
        print(f"⚠️ ERROR: Executing the SQL-Commit: {e}")
        cnx.rollback() # Mache alle Änderungen Rückgängig, wenn ein Fehler auftritt
    

while notEveryOneIsCheckedIn:

    #Requesting Barcode Scan (ID)
    print("=====================================================")
    newCheckIn = input("Bitte Barcode einscannen:\n")
    print("ID scanned: " + newCheckIn)

    # Verbindung zu Datenbank vor jedem Schleifendurchlauf aktualisieren
    cnx = connect_to_database()
    if cnx is None:
        print("Fehler bei der Datenbankverbindung. Schleife wird abgebrochen.")
        break

    cursor = cnx.cursor()

    # Zählen, wie viele Tickets noch nicht eingecheckt sind
    cursor.execute("SELECT COUNT(ID) FROM tickets WHERE Bar_Einlass = 0")
    result = cursor.fetchone()

    if result[0] > 0:
        # Wenn es Tickets gibt, die noch nicht eingecheckt sind, Barcode-Scan anfordern
        if(isEntranceOpen()):
            checkIn(newCheckIn)
        else:
            print("🚫 Aktuell wird der Einlass nicht gewährt!")
    else:
        # Wenn alle Tickets eingecheckt sind, Schleife beenden
        notEveryOneIsCheckedIn = False
        print("Alle Tickets wurden eingecheckt.")

    # Sicherstellen, dass die Verbindung nach der Verwendung geschlossen wird
    if 'cnx' in locals() and cnx.is_connected():
        cnx.close()
        #print("Datenbankverbindung geschlossen.")